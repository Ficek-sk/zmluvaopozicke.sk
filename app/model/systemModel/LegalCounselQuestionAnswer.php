<?php

namespace App\Model\SystemModel;


class LegalCounselQuestionAnswer extends BaseTable
{
    protected $tableName = 'legal_counsel_answer';


    public function findQuestionAnswers(int $questionId)
    {
        $criteria = [
            'legal_counsel_question_id' => $questionId,
            'public' => 1
        ];

        return $this->findBy($criteria);
    }

}
