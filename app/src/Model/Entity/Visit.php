<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Visit Entity
 *
 * @property int $id
 * @property \Cake\I18n\FrozenDate $date
 * @property int $completed
 * @property int $forms
 * @property int $products
 * @property int $duration
 */
class Visit extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'date' => true,
        'completed' => true,
        'forms' => true,
        'products' => true,
        'duration' => true,
    ];

    protected $_hidden = [
        'id',
        'formatted_date'
    ];

    protected $_virtual = [
        'formatted_date',
    ];

    /*
     * Get formatted date as dd-mm-yyyy
     */
    protected function _getFormattedDate(): ?string
    {
        if (isset($this->date)) {
            return $this->date->format('d-m-Y');
        }

        return null;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        // Replace date with formatted date
        if (isset($data['date'])) {
            $data['date'] = $this->formatted_date;
        }

        // show completed as boolean
        $data['completed'] = (bool) $data['completed'];

        return $data;
    }
}
