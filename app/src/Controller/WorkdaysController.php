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
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
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

         // return response
        $this->set([
            'success' => true,
            'data' => []
        ]);
        
        $this->viewBuilder()->setOption('serialize', ['success', 'data']);
    }

}
