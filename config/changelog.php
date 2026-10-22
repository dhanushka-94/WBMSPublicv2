<?php

return [
    [
        'version' => '2.4.0',
        'date' => '23 October 2026',
        'title' => 'Customer details and footer',
        'changes' => [
            'Show each customer’s name once on the details page, and stop repeating the title on the customer list.',
            'Show the current system version in the footer, with a link to this changelog.',
        ],
    ],
    [
        'version' => '2.3.0',
        'date' => '23 October 2026',
        'title' => 'Opening bill amounts',
        'changes' => [
            'Enter each customer’s starting bill amount beside the opening dial on the Initial Readings page.',
            'The amount is saved as one opening bill. A blank cell is left as it is, and no SMS is sent.',
        ],
    ],
    [
        'version' => '2.2.0',
        'date' => '23 October 2026',
        'title' => 'Initial meter readings',
        'changes' => [
            'Enter each meter’s opening dial on one Initial Readings page, linked from Bulk Meter Reading Entry.',
            'Save only the rows you fill in. A blank row is left as it is, and no bill is created.',
            'Count every active, suspended, and inactive customer on the customer list, not only the page on screen.',
        ],
    ],
    [
        'version' => '2.1.0',
        'date' => '19 October 2026',
        'title' => 'Office billing, routes, and access',
        'changes' => [
            'Assign divisions or customers to each meter reader, and report who finished or missed the route.',
            'Disconnect and reconnect a customer, with the date, reason, and staff member recorded.',
            'Price office bills and monthly bills with the customer’s own tariff, including the fixed charge. Unpaid bills can be recalculated.',
            'Store each payment as its own receipt, with daily collection, monthly collection, collection efficiency, and age analysis.',
            'Add Billing Officer and Cashier roles, and limit each role to its own screens.',
            'Send bulk SMS, a bill-generated message, and a reconnection message.',
            'Print the Dunsinane bill in English, Tamil, or both, including a narrow thermal layout.',
            'Show billed value, collection percentage, and disconnected connections on the dashboard.',
            'Add the customer register, contacts, new connections, meter faults, usage alerts, and billing summary.',
            'Show every clock and recorded time in 12-hour format with AM/PM.',
        ],
    ],
    [
        'version' => '2.0.0',
        'date' => '18 October 2026',
        'title' => 'AquaBill release',
        'changes' => [
            'Rebrand the office system to AquaBill by olexto.',
            'Generate a QR image for every meter, link it to the customer, and add a button to rebuild it.',
            'Charge the fixed amount on every bill and replace the unit slabs.',
            'Send tariff slabs and a quote to the meter-reader phone, and create the bill on this system when a reading is submitted.',
            'Add the mobile API guide for administrators.',
            'Use Sri Lanka time and full-width pages.',
        ],
    ],
    [
        'version' => '1.0.0',
        'date' => '7 July 2025',
        'title' => 'First release',
        'changes' => [
            'Customers, guarantors, divisions, and customer types.',
            'Water meters, meter readings, and bills.',
            'Users with Admin, Manager, Staff, and Meter Reader roles.',
            'SMS notices, activity logs, and system settings.',
        ],
    ],
];
