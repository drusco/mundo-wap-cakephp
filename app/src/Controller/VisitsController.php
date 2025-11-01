<?php
declare(strict_types=1);

namespace App\Controller;

use DateTime;
use Cake\Http\Exception\BadRequestException;

/**
 * Visits Controller
 *
 * @method \App\Model\Entity\Visit[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class VisitsController extends AppController
{
    /**
     * Index method
     * Lists all visits for a given date provided as query parameter.
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): void
    {
        // Accept GEt requests only when listing visits
        $this->request->allowMethod(['get']);

        $date = $this->request->getQuery('date');
        
        if(empty($date)) {
            // enforce date query parameter
            throw new BadRequestException('A date query parameter is required');
        }

        $visitDate = DateTime::createFromFormat('d-m-Y', $date);

        if(!$visitDate) {
            // enforce valid date format
            throw new BadRequestException('The date query parameter format is invalid. Expected format: dd-mm-yyyy');
        }

        // Get the visits by date and paginate
        $visits = $this->paginate(
            $this->Visits
            ->find()
            ->where([
                'date' => $visitDate
            ])
        );

        // Return the filtered visits
        $this->set('visits', $visits);
        $this->viewBuilder()->setOption('serialize', ['visits']);
    }

    /**
     * View method
     *
     * @param string|null $id Visit id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $visit = $this->Visits->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('visit'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $visit = $this->Visits->newEmptyEntity();
        if ($this->request->is('post')) {
            $visit = $this->Visits->patchEntity($visit, $this->request->getData());
            if ($this->Visits->save($visit)) {
                $this->Flash->success(__('The visit has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The visit could not be saved. Please, try again.'));
        }
        $this->set(compact('visit'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Visit id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $visit = $this->Visits->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $visit = $this->Visits->patchEntity($visit, $this->request->getData());
            if ($this->Visits->save($visit)) {
                $this->Flash->success(__('The visit has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The visit could not be saved. Please, try again.'));
        }
        $this->set(compact('visit'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Visit id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $visit = $this->Visits->get($id);
        if ($this->Visits->delete($visit)) {
            $this->Flash->success(__('The visit has been deleted.'));
        } else {
            $this->Flash->error(__('The visit could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
