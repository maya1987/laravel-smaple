/**
     * Save questionaires
     *
     * @param $visitorParamter
     * @param $data
     */
    public function saveQuestionaires($visitorParamter, $questionData, $formData, $visitorData, $queryString)
    {
       $splitName = substr($visitorData['split_path'], strpos($visitorData['split_path'], 'Plevin'));
       $uuId = $visitorParamter['uuid'];
       $userRepository = new UserRepository();
        $userId = $this->getUser($uuId,$formData,$visitorData,$queryString,$visitorParamter);
        $qId = isset($questionData['question_id']) ? $questionData['question_id'] : null;
        $optionId = isset($questionData['option_id']) ? $questionData['option_id'] : null;
        $inputAnswer = isset($questionData['input_answer']) ? $questionData['input_answer'] : null;
        $type = 'questionaire1';
        $source = 'live';
        $userData = User::where('id', $userId)->first();
        if(!isset($userData->visitor_id) && empty($userData->visitor_id)){
            $this->logInterface->writeLog('-visitorid-missing', $userId);
            return;
        }
        $visitorData = DB::table('visitors')
        ->join('split_info','visitors.split_id','=','split_info.id')
        ->where('visitors.id','=',$userData->visitor_id)
        ->first();
        $spliName = $visitorData->split_name??null;

        if ($userId) {
            if($qId == 1){
                    UserQuestionnaireAnswers::Create(
                        [
                            'user_id' => $userId,
                            'questionnaire_id' => $qId,
                            'questionnaire_option_id' => $optionId,
                            'input_answer' => $inputAnswer

                        ]
                    );
                    FollowupHistories::Create(
                        [
                            'user_id' => $userId,
                            'type' => $type,
                            'type_id' => isset($qId)?$qId:0,
                            'value' => ($optionId == 27 || $optionId == 32) ? $inputAnswer : $optionId,
                            'source' => $source
                        ]
                    );
                    $questionStatus = $this->isQuestionnaireComplete($userId);
                    if($questionStatus){
                        $this->liveSessionInterface->createUserMilestoneStats(array(
                                "user_id" => $userId,
                                "source" => $source,
                                "questions" => 1,
                            )
                        );
                    }
            }else{
                if(!empty($optionId) || !empty($inputAnswer)){
                    UserQuestionnaireAnswers::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'questionnaire_id' => $qId,

                        ],
                        [
                            'user_id' => $userId,
                            'questionnaire_option_id' => $optionId,
                            'input_answer' => $inputAnswer??null

                        ]
                    );

                }

                    if($qId == 15){
                          $historyData = [
                            'user_id' => $userId,
                            'type' => $type,
                            'type_id' => isset($qId)?$qId:0,
                            'value' => $inputAnswer??null,
                            'source' => $source
                            ];
                    }else{
                            $historyData = [
                            'user_id' => $userId,
                            'type' => $type,
                            'type_id' => isset($qId)?$qId:0,
                            'value' => $optionId,
                            'source' => $source
                            ];
                    }
                $followUpHistory = new HistoryRepository();
                $followUpHistory->insertFollowupLiveHistory($historyData);
                $questionStatus = $this->isQuestionnaireComplete($userId);
            }
            $userBanks = $userRepository->getUserDetailsFromUserId($userId);
            $questionCount  = $this->getFollowUpPendingQuestionsCount($userId);
            UserExtraDetail::where('user_id', $userId)->update(['complete_status' => 1]);
            //COA,Review,statement,Questionnaire PDF Generation
            //dispatch(new GeneratePdf($userId));
            $userRecords = User::where('id',$userId)->first();
            $userMileStoneRecords  = UserMilestoneStats::where('user_id',$userId)->first();
            $recordStatus       = isset($userRecords->record_status) ? $userRecords->record_status : 'TEST';
            $APP_ENV = env('APP_ENV');
             if ($APP_ENV == 'live' || $APP_ENV == 'pre') {
                $milestone_status   = isset($userMileStoneRecords->source) ? $userMileStoneRecords->source : 'live';
             }
             else{
                $milestone_status = 'TEST';
             }
             if($spliName  == 'Plevin/CL_PLV_2_1' || $spliName  == 'Plevin/CL_PLV_3_1'
             || $spliName  == 'Plevin/CL_PLVR4' || $spliName  == 'Plevin/CL_PLVR5_2'){
                if($questionCount > 14){
                    $this->liveSessionInterface->createUserMilestoneStats(array(
                        "user_id" => $userId,
                        "source" => $source,
                        "questions" => 1,
                    )
                      );
                 }
             }
             elseif($spliName  == 'Plevin/CL_PLVR1'){
                if($questionCount > 15){
                    $this->liveSessionInterface->createUserMilestoneStats(array(
                        "user_id" => $userId,
                        "source" => $source,
                        "questions" => 1,
                    )
                      );
                 }

             }
             elseif($spliName  == 'Plevin/CL_PLVR6'){
                if($questionCount > 13){
                    $this->liveSessionInterface->createUserMilestoneStats(array(
                        "user_id" => $userId,
                        "source" => $source,
                        "questions" => 1,
                    )
                      );
                 }

             }
             else{
                if($questionCount > 14){
                    $this->liveSessionInterface->createUserMilestoneStats(array(
                        "user_id" => $userId,
                        "source" => $source,
                        "questions" => 1,
                    )
                      );
                 }

             }


    }
    }
