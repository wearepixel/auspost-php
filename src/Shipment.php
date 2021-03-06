<?php

namespace Joelwmale\Auspost;

/**
 * A shipment, made up of one or more parcels
 *
 * @package Joelwmale\Auspost
 * @author Josh Marshall <josh@jmarshall.com.au>
 *
 * @property Auspost $_auspost
 * @property string $movement_type
 * @property string $shipment_reference
 * @property string $customer_reference_1
 * @property string $customer_reference_2
 * @property bool $email_tracking_enabled
 * @property Address $to
 * @property Address $from
 * @property Parcel[] $parcels
 * @property string $product_id The AusPost product to use for this shipment
 * @property string $shipment_id The AusPost generated id when lodged
 * @property \DateTime $shipment_lodged_at The time the shipment was lodged
 */
class Shipment
{
    private $_auspost;

    public $from;
    public $to;
    public $parcels = [];
    
    public $movement_type;
    public $shipment_reference;
    public $customer_reference_1 = '';
    public $customer_reference_2 = '';
    public $email_tracking_enabled = true;
    public $delivery_instructions = '';

    public $product_id;
    public $shipment_id;
    public $shipment_lodged_at;

    public function __construct($api)
    {
        $this->_auspost = $api;
    }

    /**
     * Add the To address
     *
     * @param Address $address The address to deliver to
     *
     * @return $this
     */
    public function setTo(Address $address): self
    {
        $this->to = $address;

        return $this;
    }
    /**
     * Add the From address
     *
     * @param Address $address The address to send from
     *
     * @return $this
     */
    public function setFrom(Address $address): self
    {
        $this->from = $address;

        return $this;
    }

    /**
     * Set movement type for the shipment
     *
     * @param string $movementType
     *
     * @return $this
     */
    public function setMovementType($movement_type): self
    {
        $this->movement_type = $movement_type;

        return $this;
    }

    /**
     * Add a new parcel to the shipment
     *
     * @param Parcel $data
     *
     * @return $this
     */
    public function addParcel(Parcel $data): self
    {
        $this->parcels[] = $data;

        return $this;
    }

    /**
     *
     * @return \Joelwmale\Auspost\Quote[]
     * @throws \Exception
     */
    public function getQuotes()
    {
        $request = [
            'from' => [
                'postcode' => $this->from->postcode,
                'country' => $this->from->country,
            ],
            'to' => [
                'postcode' => $this->to->postcode,
                'country' => $this->to->country,
            ],
            'items' => [],
        ];
        foreach ($this->parcels as $parcel) {
            $item = [
                'item_reference' => $parcel->item_reference,
                'length' => $parcel->length,
                'height' => $parcel->height,
                'width' => $parcel->width,
                'weight' => $parcel->weight,
            ];
            if ($parcel->value) {
                $item['features'] = [
                    'TRANSIT_COVER' => [
                        'attributes' => [
                            'cover_amount' => $parcel->value,
                        ]
                    ]
                ];
            }
            $request['items'][] = $item;
        }
        return $this->_auspost->getQuotes($request);
    }

    /**
     * Lodge a shipment with Auspost
     *
     * @return $this
     */
    public function lodgeShipment(): self
    {
        $request = [
            'shipment_reference' => $this->shipment_reference,
            'customer_reference_1' => $this->customer_reference_1,
            'customer_reference_2' => $this->customer_reference_2,
            'email_tracking_enabled' => $this->email_tracking_enabled,
            'movement_type' => $this->movement_type,

            'from' => [
                'name'          => $this->from->name,
                'business_name' => $this->from->business_name,
                'lines'         => $this->from->lines,
                'suburb'        => $this->from->suburb,
                'state'         => $this->from->state,
                'postcode'      => $this->from->postcode,
                'country'       => $this->from->country,
                'phone'         => $this->from->phone,
                'email'         => $this->from->email,
            ],

            'to' => [
                'name'          => $this->to->name,
                'business_name' => $this->to->business_name,
                'lines'         => $this->to->lines,
                'suburb'        => $this->to->suburb,
                'state'         => $this->to->state,
                'postcode'      => $this->to->postcode,
                'country'       => $this->to->country,
                'phone'         => $this->to->phone,
                'email'         => $this->to->email,
                'delivery_instructions' => $this->delivery_instructions,
            ],

            'items' => [],
        ];

        if (is_array($this->parcels) && count($this->parcels)) {
            foreach ($this->parcels as $parcel) {
                $item = [
                    'item_reference' => $parcel->item_reference,
                    'product_id'     => $this->product_id,
                    'length'         => $parcel->length,
                    'height'         => $parcel->height,
                    'width'          => $parcel->width,
                    'weight'         => $parcel->weight,
                    'contains_dangerous_goods' => $parcel->contains_dangerous_goods,
                    'authority_to_leave' => $parcel->authority_to_leave,
                    'safe_drop_enabled' => $parcel->safe_drop_enabled,
                    'allow_partial_delivery' => $parcel->allow_partial_delivery,
                ];
                if ($parcel->value) {
                    $item['features'] = [
                        'TRANSIT_COVER' => [
                            'attributes' => [
                                'cover_amount' => $parcel->value,
                            ]
                        ]
                    ];
                }
                $request['items'][] = $item;
            }
        }

        $response = $this->_auspost->shipments(['shipments' => $request]);

        foreach ($response['shipments'] as $shipment) {
            $this->shipment_id = $shipment['shipment_id'];
            $this->shipment_lodged_at = new \DateTime($shipment['shipment_creation_date']);
            foreach ($shipment['items'] as $item) {
                foreach ($this->parcels as $key => $parcel) {
                    if ($parcel->item_reference != $item['item_reference']) {
                        continue;
                    }
                    $this->parcels[$key]->item_id = $item['item_id'];
                    $this->parcels[$key]->tracking_article_id = $item['tracking_details']['article_id'];
                    $this->parcels[$key]->tracking_consignment_id = $item['tracking_details']['consignment_id'];
                }
            }
        }

        return $this;
    }

    /**
     * Get the labels for this shipment
     *
     * @param LabelType $labelType
     *
     * @return string url to label
     *
     * @throws \Exception
     */
    public function getLabel(LabelType $labelType): string
    {
        return $this->_auspost->getLabels([$this->shipment_id], $labelType);
    }

    /**
     * Delete this shipment
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function deleteShipment(): bool
    {
        return $this->_auspost->deleteShipment($this->shipment_id);
    }
}
