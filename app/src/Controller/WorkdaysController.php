<?php
declare(strict_types=1);

namespace App\Controller;

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
    public function index()
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
     * Edit method
     *
     * @param string|null $id Workday id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $workday = $this->Workdays->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $workday = $this->Workdays->patchEntity($workday, $this->request->getData());
            if ($this->Workdays->save($workday)) {
                $this->Flash->success(__('The workday has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The workday could not be saved. Please, try again.'));
        }
        $this->set(compact('workday'));
    }

}
