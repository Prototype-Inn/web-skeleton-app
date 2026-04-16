<?php

declare(strict_types=1);

// This file defines the ABAC policies for the application.
// Policies are an array where keys can represent resource identifiers,
// and values are arrays of rules or conditions.

return [
    // Example policy: Allow 'admin' role to access 'homepage' resource
    'homepage_access' => [
        'description' => 'Policy to control access to the home page',
        'rules' => [
            [
                'target' => [
                    'resource.name' => 'homepage',
                    'action.name' => 'view'
                ],
                'condition' => 'subject.roles has "admin"', // Assuming 'roles' is an array of strings
                'effect' => 'permit'
            ],
            [
                'target' => [
                    'resource.name' => 'homepage',
                    'action.name' => 'view'
                ],
                'condition' => 'subject.isAuthenticated is true', // Allow authenticated users
                'effect' => 'permit'
            ],
            [
                'target' => [
                    'resource.name' => 'homepage',
                    'action.name' => 'view'
                ],
                'effect' => 'deny' // Default deny for homepage view if no permit rule matches
            ]
        ]
    ],
    // More policies can be added here
];
