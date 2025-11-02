<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Visit;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Visits Model
 *
 * @method \App\Model\Entity\Visit newEmptyEntity()
 * @method \App\Model\Entity\Visit newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Visit[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Visit get($primaryKey, $options = [])
 * @method \App\Model\Entity\Visit findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Visit patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Visit[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Visit|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Visit saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Visit[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Visit[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Visit[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Visit[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class VisitsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('visits');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->hasOne('Addresses', [
            'class' => 'Addresses',
            'foreignKey' => 'foreign_id',
            'conditions' => ['Addresses.foreign_table' => 'visits'],
            'dependent' => true,
            'cascadeFallbacks' => true,
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->date('date', ['dmy'], 'Expected format: dd-mm-yyyy')
            ->requirePresence('date', 'create')
            ->notEmptyDate('date');

        $validator
            ->boolean('completed')
            ->allowEmpty('completed');

        $validator
            ->integer('forms')
            ->requirePresence('forms', 'create')
            ->notEmptyString('forms');

        $validator
            ->integer('products')
            ->requirePresence('products', 'create')
            ->notEmptyString('products');

        $validator
            ->integer('duration')
            ->notEmptyString('duration');

        $this->validatorAddress($validator);

        return $validator;
    }

    /**
     * Custom validation for address field.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validatorAddress(Validator $validator): Validator
    {
        $validator
            ->requirePresence('address', 'create', 'Address is required')
            ->notEmptyArray('address', 'Address cannot be empty')
            ->add('address', 'type', [
                'rule' => fn($value) => is_array($value),
                'message' => 'Address must be an array',
            ]);

        // Load the Addresses table to get its validator
        $addressesTable = TableRegistry::getTableLocator()->get('Addresses');
        $addressValidator = $addressesTable->getValidator('default');

        // Add the nested validator for the address field
        $validator->addNested('address', $addressValidator);

        return $validator;
    }

    public function beforeMarshal(EventInterface $event, \ArrayObject $data, \ArrayObject $options): void
    {
        // convert boolean completed to integer
        if (isset($data['completed']) && is_bool($data['completed'])) {
            $data['completed'] = (int) $data['completed'];
        }

        // convert date from string to DateTime
        if (isset($data['date'])) {
            $data['date'] = \DateTime::createFromFormat('d-m-Y', $data['date']);
        }
    }

    public function beforeSave(EventInterface $event, Visit $visit, \ArrayObject $options): void
    {
        // Set the duration value based on the forms and product minutes
        $formsMinutes = (int)$visit->forms * 15;
        $productsMinutes = (int) $visit->products * 5;
        $newDuration = $formsMinutes + $productsMinutes;

        // Find or create a workday using the visit date
        $workdaysTable = TableRegistry::getTableLocator()->get('Workdays');
        
        /** @var \App\Model\Entity\Workday */
        $workday = $workdaysTable->findOrCreate(
            ['date' => $visit->date],
            function ($entity) use ($visit): void {
                // set the date on the newly created workday
                $entity->set('date', $visit->date);
            }
        );

        if (!$visit->isNew()) {
            // substract previous duration and visit
            $workday->set('duration', $workday->duration - $visit->duration);
            $workday->set('visits', $workday->visits - 1);
        }

        // set new values on the workday
        $workday->set('duration', $workday->duration + $newDuration);
        $workday->set('visits', $workday->visits + 1);

        // save the workday to the database
        $workdaysTable->saveOrFail($workday);

        $visit->set('duration', $newDuration);
    }

    public function afterSave(EventInterface $event, Visit $visit, \ArrayObject $options): void
    {
        // If the visit has an associated address, ensure it's linked correctly
        if ($visit->has('address')) {
            $addressData = $visit->get('address');

            $addressesTable = TableRegistry::getTableLocator()->get('Addresses');
            $address = $addressesTable->newEntity($addressData);

            // Add foreign key fields to address data
            $address->set('foreign_table', 'visits');
            $address->set('foreign_id', $visit->id);

            // remove old addresses linked to the current visit
            $addressesTable->deleteAll([
                'foreign_table' => 'visits',
                'foreign_id' => $visit->id
            ]);

            // Save the address and link it to the visit
            $addressesTable->saveOrFail($address);
        }
    }
}
