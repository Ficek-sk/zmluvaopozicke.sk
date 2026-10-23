<?php declare(strict_types=1);

namespace App\Model\SystemModel;

class LegalCounselQuestionRelatedUrl extends BaseTable
{
    protected $tableName = 'legal_counsel_question_related_url';

    public function getByQuestionId(int $id)
    {
        $questions = $this->findBy([
            'legal_counsel_question_id' => $id
        ])

            ->select('title, url, snippet')
            ->fetchAll();

        return $questions;
    }
}
