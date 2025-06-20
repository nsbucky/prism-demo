<?php

declare(strict_types=1);

namespace App\Services;

use Prism\Prism\Facades\Tool;

class ConversationToolService
{
    public static function getExtractionTools(): array
    {
        return [
            Tool::as('confirm_name_extraction')
                ->for('Confirm extracted name information from user response')
                ->withStringParameter('firstName', 'The extracted first name')
                ->withStringParameter('lastName', 'The extracted last name')
                ->using(function (?string $firstName = null, ?string $lastName = null): string {
                    // The LLM has already done the extraction!
                    // This tool just validates and returns the data

                    if (!$firstName || !$lastName) {
                        return json_encode([
                            'success' => false,
                            'message' => 'Both first and last name are required',
                        ]);
                    }

                    return json_encode([
                        'success' => true,
                        'data'    => [
                            'firstName' => $firstName,
                            'lastName'  => $lastName,
                            'fullName'  => "{$firstName} {$lastName}",
                        ],
                    ]);
                }),

            Tool::as('confirm_date_extraction')
                ->for('Confirm extracted date information from user response')
                ->withStringParameter('startDate', 'The start date in YYYY-MM-DD format')
                ->withStringParameter('endDate', 'The end date in YYYY-MM-DD format')
                ->using(fn(?string $startDate = null, ?string $endDate = null): string => json_encode([
                    'success' => true,
                    'data'    => [
                        'startDate' => $startDate,
                        'endDate'   => $endDate,
                        'duration'  => $startDate && $endDate
                            ? \Carbon\Carbon::parse($startDate)->diffInDays($endDate).' days'
                            : null,
                    ],
                ])),

            Tool::as('confirm_address_extraction')
                ->for('Confirm extracted address information from user response')
                ->withStringParameter('streetAddress', 'The street address including house/building number')
                ->withStringParameter('streetAddress2', 'Apartment, suite, unit, building, floor, etc.')
                ->withStringParameter('city', 'The city or town name')
                ->withStringParameter('stateProvince', 'The state, province, region, or prefecture')
                ->withStringParameter('postalCode', 'The postal code, ZIP code, or postcode')
                ->withStringParameter('country', 'The country name or ISO code')
                ->using(function (
                    ?string $streetAddress = null,
                    ?string $streetAddress2 = null,
                    ?string $city = null,
                    ?string $stateProvince = null,
                    ?string $postalCode = null,
                    ?string $country = null,
                ): string {
                    // Validate required fields
                    $missingFields = [];
                    if (!$streetAddress) {
                        $missingFields[] = 'street address';
                    }
                    if (!$city) {
                        $missingFields[] = 'city';
                    }
                    if (!$country) {
                        $missingFields[] = 'country';
                    }

                    // Some countries don't use postal codes or states
                    $requiresPostalCode = !in_array(mb_strtoupper($country ?? ''), ['IE', 'HK', 'AE']);
                    $requiresState      = in_array(mb_strtoupper($country ?? ''),
                        ['US', 'USA', 'CA', 'AU', 'MX', 'BR', 'IN']);

                    if ($requiresPostalCode && !$postalCode) {
                        $missingFields[] = 'postal code';
                    }

                    if ($requiresState && !$stateProvince) {
                        $missingFields[] = 'state/province';
                    }

                    if (!empty($missingFields)) {
                        return json_encode([
                            'success' => false,
                            'message' => 'Missing required fields: '.implode(', ', $missingFields),
                        ]);
                    }

                    // Format complete address
                    $fullAddress = $streetAddress;
                    if ($streetAddress2) {
                        $fullAddress .= ', '.$streetAddress2;
                    }
                    $fullAddress .= ', '.$city;
                    if ($stateProvince) {
                        $fullAddress .= ', '.$stateProvince;
                    }
                    if ($postalCode) {
                        $fullAddress .= ' '.$postalCode;
                    }
                    $fullAddress .= ', '.$country;

                    return json_encode([
                        'success' => true,
                        'data'    => [
                            'streetAddress'  => $streetAddress,
                            'streetAddress2' => $streetAddress2,
                            'city'           => $city,
                            'stateProvince'  => $stateProvince,
                            'postalCode'     => $postalCode,
                            'country'        => $country,
                            'fullAddress'    => $fullAddress,
                            'formatted'      => [
                                'line1' => $streetAddress,
                                'line2' => $streetAddress2 ?: '',
                                'line3' => $city.($stateProvince ? ', '.$stateProvince : '').($postalCode ? ' '.$postalCode : ''),
                                'line4' => $country,
                            ],
                        ],
                    ]);
                }),
        ];
    }
}
