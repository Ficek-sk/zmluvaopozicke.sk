<?php

namespace App\Model\SystemModel;

class LegalCounselQuestion extends BaseTable
{
    protected $tableName = 'legal_counsel_question';
    public const WEB_FICEK_ID = 2;

    /**
     * @param int $limit
     * @return array|\Nette\Database\Table\IRow[]|\Nette\Database\Table\Selection
     */
    public function getRecentQuestions(int $limit)
    {
        $questions = $this->findBy([
            'answered IS NOT NULL',
            'pricing = "free"',
            'public = 1',
            'web_id' => self::WEB_FICEK_ID
        ])
            ->where('answered < NOW()')
            ->select('id, slug, parent_question_id, answered, name, text')
            ->limit(20)
            ->order('answered DESC')
            ->fetchAll();

        $iterator = 0;
        $result = [];

        foreach ($questions as $question) {
            $replaced = $this->replaceByParentIfExists($question);
            $result[$replaced['slug']] = $this->replaceByParentIfExists($question);

            if($iterator === $limit || count($result) === $limit){
                return $result;
            }
        }

        return $result;
    }

    /**
     * @param $question
     * @return array
     */
    private function replaceByParentIfExists($question)
    {
        if ($question['parent_question_id'] !== null) {

            $parentQuestion = $this->find($question['parent_question_id'])->where('answered < NOW()')->fetch();

            if($parentQuestion === false){
                return null;
            }

            $result = [];
            $result['slug'] = $parentQuestion['slug'];
            $result['name'] = $parentQuestion['name'];
            $result['answered'] = $question['answered'];

            return $result;
        }

        return $question;
    }

    public function findRootQuestion($slug)
    {
        $criteria = [
            'slug' => $slug
        ];

        $q = $this->findOneBy($criteria)->where('answered < NOW()')->fetch();

        if ($q['answered'] !== null && ($q['parent_question_id'] === null || $q['redirect'] !== null) && $q['public'] === 1 && $q['web_id'] === self::WEB_FICEK_ID) {
            if($q['answered'] < new \DateTime()){
                return $q;
            }
        }

        return null;
    }

    /**
     * @param int $rootQuestionId
     * @return bool|mixed|\Nette\Database\Table\IRow
     */
    public function getSubQuestions(int $rootQuestionId)
    {
        $mappedSubQuestions = [];

        $criteria = [
            'parent_question_id' => $rootQuestionId,
            'pricing' => 'free',
            'answered IS NOT NULL',
            'public' => 1,
            'web_id' => self::WEB_FICEK_ID
        ];

        $subQuestions = $this->findBy($criteria)->where('answered < NOW()')->order('answered DESC');

        foreach ($subQuestions as $subQuestion) {
            $subQuestionMapped['subQuestion'] = $subQuestion;
            $subQuestionMapped['answers'] = $this->findQuestionAnswers($subQuestion['id']);

            $mappedSubQuestions[] = $subQuestionMapped;
        }

        return $mappedSubQuestions;
    }

    private function findQuestionAnswers(int $questionId)
    {
        $answerTable = $this->context->table('legal_counsel_answer');

        $criteria = [
            'legal_counsel_question_id' => $questionId,
            'public' => 1
        ];

        return $answerTable->where($criteria)->fetchAll();
    }
}
