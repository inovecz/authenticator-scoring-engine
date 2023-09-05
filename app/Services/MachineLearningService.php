<?php

namespace App\Services;

use Rubix\ML\Datasets\Labeled;
use Phpml\Exception\LibsvmCommandException;
use Rubix\ML\Classifiers\LogisticRegression;
use Rubix\ML\NeuralNet\Optimizers\StepDecay;
use Rubix\ML\CrossValidation\Reports\AggregateReport;
use Rubix\ML\CrossValidation\Reports\ConfusionMatrix;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;

class MachineLearningService
{
    protected Labeled $dataset;
    protected Labeled $testing;
    protected LogisticRegression $estimator;

    public function setDataset(array $inputs, array $outputs)
    {
        array_map(static fn($input) => [1], $inputs);
        $this->dataset = new Labeled($inputs, $outputs);

        [$dummy, $this->testing] = $this->dataset->stratifiedSplit(0.8);

        $this->estimator = new LogisticRegression(256, new StepDecay(0.01, 100));
    }

    /**
     * @throws LibsvmCommandException
     */
    public function predict(array $predictFor = null): array
    {
        $labels = $this->dataset->labels();

        $uniqueLabel = array_values(array_unique($labels));
        if (!empty($labels) && count($uniqueLabel) === 1) {
            return [
                'successful' => $uniqueLabel[0] === 'successful' ? 1 : 0,
                'unsuccessful' => $uniqueLabel[0] === 'unsuccessful' ? 1 : 0,
            ];
        }

        $this->estimator->train($this->dataset);

        if ($predictFor) {
            $dataset = new Labeled([$predictFor], [null]);
            $this->estimator->proba($dataset);
        }

        $report = new AggregateReport([new MulticlassBreakdown(), new ConfusionMatrix(),]);

        $predictions = $this->estimator->predict($this->testing);

        $results = $report->generate($predictions, $this->testing->labels());

        $uniqueOutputs = array_unique($this->dataset->labels());

        $result = [];
        foreach ($uniqueOutputs as $output) {
            $result[$output] = $results[0]['classes'][$output]['proportion'];
        }

        return $result;
    }
}
