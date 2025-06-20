<?php

declare(strict_types=1);

use App\Services\ConversationToolService;

test('extraction tools are properly configured', function () {
    $tools = ConversationToolService::getExtractionTools();

    expect($tools)->toHaveCount(3);

    $toolNames = array_map(fn($tool) => $tool->name(), $tools);
    expect($toolNames)->toContain('confirm_name_extraction');
    expect($toolNames)->toContain('confirm_date_extraction');
    expect($toolNames)->toContain('confirm_address_extraction');
});

test('name extraction tool validates required fields', function () {
    $tools = ConversationToolService::getExtractionTools();
    /**
     * @var \Prism\Prism\Tool $nameTool
     */
    $nameTool = collect($tools)->first(fn($tool) => $tool->name() === 'confirm_name_extraction');

    // Get the function from the tool

    // Test with missing last name
    $result = json_decode($nameTool->handle('John'), true);
    expect($result['success'])->toBeFalse();
    expect($result['message'])->toContain('Both first and last name are required');

    // Test with complete data
    $result = json_decode($nameTool->handle('John', 'Doe'), true);
    expect($result['success'])->toBeTrue();
    expect($result['data']['fullName'])->toBe('John Doe');
});

test('address extraction tool validates based on country', function () {
    $tools = ConversationToolService::getExtractionTools();

    /**
     * @var \Prism\Prism\Tool $addressTool
     */
    $addressTool = collect($tools)->first(fn($tool) => $tool->name() === 'confirm_address_extraction');

    // Test US address without state
    $result = json_decode($addressTool->handle(
        '123 Main St',
        null,
        'New York',
        null,
        '10001',
        'US'
    ), true);
    expect($result['success'])->toBeFalse();
    expect($result['message'])->toContain('state/province');

    // Test Ireland address (no postal code required)
    $result = json_decode($addressTool->handle(
        '10 Downing Street',
        null,
        'Dublin',
        null,
        null,
        'IE'
    ), true);
    expect($result['success'])->toBeTrue();
});

test('date extraction tool returns duration', function () {
    $tools = ConversationToolService::getExtractionTools();

    /**
     * @var \Prism\Prism\Tool $dateTool
     */
    $dateTool = collect($tools)->first(fn($tool) => $tool->name() === 'confirm_date_extraction');

    $result = json_decode($dateTool->handle('2024-01-01', '2024-01-10'), true);

    expect($result['success'])->toBeTrue();
    expect($result['data']['duration'])->toBe('9 days');
});