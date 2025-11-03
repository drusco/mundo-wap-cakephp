<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\BadRequestException;

/**
 * Workdays Controller
 *
 * @property \App\Model\Table\WorkdaysTable $Workdays
 * @method \App\Model\Entity\Workday[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class WorkdaysController extends AppController
{
    /**
     * Index method
     * Lists all the available working days
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): void
    {
        // Accept GEt requests only when listing visits
        $this->request->allowMethod(['get']);

        // find the workdays entries
        $workdays = $this->paginate(
            $this->fetchTable('Workdays')->find()
        );

        // return work days
        $this->set([
            'success' => true,
            'data' => $workdays
        ]);

        $this->viewBuilder()->setOption('serialize', ['success', 'data']);
    }

    /**
     * End the desired workday
     *
     * @return \Cake\Http\Response|null|void Renders view on successful closing.
     * @throws \Cake\Http\Exception\InternalErrorException When the update fails.
     * @throws \Cake\Http\Exception\BadRequestException When the incoming payload is invalid.
     */
    public function end(): void
    {
        // Accept PUT requests only
        $this->request->allowMethod(['put']);

        $date = $this->request->getQuery('date');
        
        if(empty($date)) {
            // enforce date query parameter
            throw new BadRequestException('A date query parameter is required');
        }

        $dateFormat = 'd-m-Y';
        $dateObject = \DateTime::createFromFormat($dateFormat, $date);
        $isValidDate = $dateObject && $dateObject->format($dateFormat) === $date;

        // validate date query parameter
        if (!$isValidDate) {
            throw new BadRequestException('Date query parameter expects the format: dd-mm-yyyy');
        }

        // move all pending visits to the next available dates
        $success = $this->movePendingVisits($dateObject);

         // return response
        $this->set([
            'success' => $success,
            'message' => $success ? 'Workday was successfully closed' : 'Workday could not be closed'
        ]);

        $this->response->withStatus($success ? 200 : 500);
        
        $this->viewBuilder()->setOption('serialize', ['success', 'message']);
    }

    /**
     * Move all pending visits to the next available date
     * 
     * @param \DateTime A date to find pending visits
     * @return bool Returnns true all visits were moved, false otherwise
     */
    private function movePendingVisits(\DateTime $date): bool
    {
        $maxDuration = 60 * 8;
        $visitsTable = $this->fetchTable('Visits');
        $workdaysTable = $this->fetchTable('Workdays');
        $movedVisitsCount = 0;

        $pendingVisits = $visitsTable->find()
            ->where([
                'date' => $date,
                'completed' => 0
            ])
            ->orderAsc('id')
            ->all();

        if (!$pendingVisits->count()) {
            // no pending visits were found
            return true;
        }

        foreach ($pendingVisits as $visit) {
            $formsMinutes = $visit->forms * 15;
            $productsMinutes = $visit->products * 5;
            $visitDuration = $formsMinutes + $productsMinutes;

            $nextDate = (clone $date)->modify('+1 day');

            // loop through the workdays to check if visits fit
            while (true) {
                /** @var \App\Model\Entity\Workday */
                $workday = $workdaysTable->findOrCreate([
                    'date' => $nextDate
                ]);

                // get current duration
                $workdayDuration = $workday->duration;

                // check if there is space in the next workday
                if ($workdayDuration + $visitDuration <= $maxDuration) {
                    // move the visit to the next date
                    $visit->set('date', $nextDate);
                    if ($visitsTable->save($visit)) {
                        $movedVisitsCount++;
                    }
                    break;
                }

                // try to move to the next date
                $nextDate = (clone $nextDate)->modify('+1 day');
            }
        }

        // check whether the workday is free of pending visits
        if ($pendingVisits->count() !== $movedVisitsCount) {
            // some visits could not be moved to a next date
            return false;
        }

        return true;
    }

}
