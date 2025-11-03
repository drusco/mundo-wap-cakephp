<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\Interface\PostalCodeServiceInterface;
use Cake\Http\Exception\InternalErrorException;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;

/**
 * Visits Controller
 *
 * @method \App\Model\Entity\Visit[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class VisitsController extends AppController
{
    /**
     * Lists all visits for a given date provided as query parameter.
     *
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     * @throws \Cake\Http\Exception\InternalErrorException When the update fails.
     * @throws \Cake\Http\Exception\BadRequestException When the incoming payload is invalid.
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
     * Create a new visit record
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     * @throws \Cake\Http\Exception\InternalErrorException When the update fails.
     * @throws \Cake\Http\Exception\BadRequestException When the incoming payload is invalid.
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
            $errors = $visit->getErrors();
            // throw if saving to the database fails
            if (isset($errors['duration']) && isset($errors['duration']['maxDuration'])) {
                throw new BadRequestException($errors['duration']['maxDuration']);
            }
            throw new InternalErrorException(json_encode($errors));
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
     * Updates and existing visit
     *
     * @param string|null $id Visit id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     * @throws \Cake\Http\Exception\InternalErrorException When the update fails.
     * @throws \Cake\Http\Exception\BadRequestException When the incoming payload is invalid.
     */
    public function edit(PostalCodeServiceInterface $postalCodeService, $id = null): void
    {
        // enforce PATCH and PUT method for editing an existing visit
        $this->request->allowMethod(['patch', 'put']);

        $data = $this->request->getData();
        $addressData = $data['address'] ?? [];

        $visit = $this->fetchTable('Visits')->find()
            ->where(['id' => $id])
            ->first();

        if (empty($visit)) {
            // throw an error if the visit cannot be found
            throw new NotFoundException('Visit could not be found');
        }

        // update the current visit with new data
        $visit = $this->Visits->patchEntity($visit, $data);

        if($visit->hasErrors()) {
            // throw is the visit fields are invalid
            throw new BadRequestException(json_encode($visit->getErrors()));
        }

        if (!empty($addressData)) {
            // find the information related to the postal_code
            $postalCodeData = $postalCodeService->fetchPostalCode($addressData['postal_code']);

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

            // Set address data into the current visit
            $visit->set('address', $addressData);
        }

        if (!$this->Visits->save($visit)) {
            $errors = $visit->getErrors();
            // throw if saving to the database fails
            if (isset($errors['duration']) && isset($errors['duration']['maxDuration'])) {
                throw new BadRequestException($errors['duration']['maxDuration']);
            }
            throw new InternalErrorException(json_encode($errors));
        }

        // indicate that the visit was updated correctly
        $this->set([
            'success' => true,
            'message' => 'The visit has been updated.'
        ]);

        $this->viewBuilder()->setOption('serialize', ['success', 'message']);
    }

}
