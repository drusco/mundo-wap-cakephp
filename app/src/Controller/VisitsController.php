<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\Interface\PostalCodeServiceInterface;
use Cake\Http\Exception\InternalErrorException;
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

        $visitDate = \DateTime::createFromFormat('d-m-Y', $date);

        if(!$visitDate) {
            // enforce valid date format
            throw new BadRequestException('The date query parameter format is invalid. Expected format: dd-mm-yyyy');
        }

        // Get the visits by date and paginate
        $visits = $this->fetchTable('Visits')
            ->find()
            ->contain(['Addresses'])
            ->where(['date' => $visitDate]
        );

        // Return the filtered visits
        $this->set('visits', $visits);
        $this->viewBuilder()->setOption('serialize', ['visits']);
    }

    /**
     * Add method
     * Create a new visit record
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add(PostalCodeServiceInterface $postalCodeService): void
    {
        // enforce POST method for adding a new visit
        $this->request->allowMethod(['post']);

        $data = $this->request->getData();
        $addressData = $data['address'];

        // create and patch a new visit entity
        $visit = $this->Visits->newEmptyEntity();
        $visit = $this->Visits->patchEntity($visit, $data);

        if($visit->hasErrors()) {
            // throw if validation errors exist
            throw new BadRequestException( json_encode($visit->getErrors()) );
        }

        // find the information related to the postal_code
        $postalCodeData = $postalCodeService->fetchPostalCode($addressData['postal_code']);

        if (empty($postalCodeData)) {
            // throw an error when the posta_code is not found
            throw new BadRequestException('CEP não encontrado');
        }

        // update the address data with the postal code service values

        $addressData['city'] = $postalCodeData['city'];
        $addressData['state'] = $postalCodeData['state'];

        if (!isset($addressData['sublocality'])) {
            $addressData['sublocality'] = $postalCodeData['sublocality'];
        }

        if (!isset($addressData['street'])) {
            $addressData['street'] = $postalCodeData['street'];
        }

        if (!isset($addressData['complement'])) {
            $addressData['commplement'] = $postalCodeData['complement'] ?? '';
        }

        // Attach the address to the visit
        $visit->set('address', $addressData);
        
        if (!$this->Visits->save($visit)) {
        // throw if saving to the database fails
           throw new InternalErrorException('The visit could not be saved.');
        }

        // Indicate success
        $this->set([
            'success' => true,
            'message' => 'The visit has been saved.'
        ]);

        // Add the created status code
        $this->response = $this->response->withStatus(201);
        // Serialize the success response
        $this->viewBuilder()->setOption('serialize', ['success', 'message']);
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

}
