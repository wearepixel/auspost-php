<?php

namespace Joelwmale\Auspost;

class LabelType
{
    public const LAYOUT_A4_ONE_PER_PAGE = 'A4-1pp';
    public const LAYOUT_A4_TWO_PER_PAGE = 'A4-2pp';
    public const LAYOUT_A4_THREE_PER_PAGE = 'A4-3pp';
    public const LAYOUT_A4_FOUR_PER_PAGE = 'A4-4pp';
    public const LAYOUT_A6_ONE_PER_PAGE = 'A6-1PP';
    public const LAYOUT_A6_THERMAL = 'THERMAL-LABEL-A6-1PP';

    public const FORMAT_PDF = 'PDF';
    public const FORMAT_ZPL = 'ZPL';

    public $layout_type = self::LAYOUT_A4_ONE_PER_PAGE;
    public $format = self::FORMAT_PDF;
    public $branded = true;
    public $left_offset = 0;
    public $top_offset = 0;

    public const AVAILABLE_LABELS = [
        Auspost::PARCEL_POST => [
            self::LAYOUT_A4_ONE_PER_PAGE,
            self::LAYOUT_A4_FOUR_PER_PAGE,
            self::LAYOUT_A6_THERMAL
        ],
        Auspost::EXPRESS_POST => [
            self::LAYOUT_A4_ONE_PER_PAGE,
            self::LAYOUT_A4_THREE_PER_PAGE,
            self::LAYOUT_A6_THERMAL
        ],
        Auspost::INTERNATIONAL => [
            self::LAYOUT_A4_ONE_PER_PAGE,
            self::LAYOUT_A4_FOUR_PER_PAGE,
            self::LAYOUT_A6_THERMAL
        ],
        Auspost::STAR_TRACK => [
            self::LAYOUT_A4_ONE_PER_PAGE,
            self::LAYOUT_A4_TWO_PER_PAGE,
            self::LAYOUT_A4_FOUR_PER_PAGE,
            self::LAYOUT_A6_THERMAL
        ],
        Auspost::STAR_TRACK_COURIER => [
            self::LAYOUT_A4_ONE_PER_PAGE,
            self::LAYOUT_A4_FOUR_PER_PAGE,
            self::LAYOUT_A6_THERMAL
        ],
        Auspost::ON_DEMAND => [
            self::LAYOUT_A4_ONE_PER_PAGE,
            self::LAYOUT_A4_FOUR_PER_PAGE,
            self::LAYOUT_A6_THERMAL
        ],
    ];

    public function __construct($details)
    {
        foreach ($details as $key => $data) {
            $this->$key = $data;
        }
    }
}
