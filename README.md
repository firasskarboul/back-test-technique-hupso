# Project README

## Overview

### Backend

A repository with a Symfony (SF) project setup will be provided. It includes:

- **Book Entity & Migration**: A `Book` entity with its associated migration and fixtures.
- **List Endpoint with Filters**: Implement an endpoint for listing books with filters for title, category, and publication year.
- **Booking Entity & Creation Endpoint**: Set up a `Booking` entity and an endpoint for creating a booking.


# Endpoint & Entity Implementation Guide

## Overview

This guide outlines the steps for setting up a `List` endpoint with specific filters for a book catalog, and implementing a `Booking` entity along with its creation endpoint. These components are essential for enabling users to browse and interact with the available books in our library system.

### List Endpoint with Filters

The `List` endpoint should allow users to filter books based on title, category, and publication year. This feature enhances the user experience by providing a tailored browsing option.

#### Implementation Steps:

1. **Define Route**: Create a new route in your controller for the `List` endpoint.

2. **Controller Logic**: In the controller method associated with the `List` endpoint, add logic to accept query parameters for title, genre, and publication year.

3. **Query Handling**: Modify the repository method to handle the filters. Use conditional statements to add where clauses to your query builder based on the provided filters.

4. **Response**: Ensure the endpoint returns a list of books that match the filters. The response should be in JSON format.

### Booking Entity & Creation Endpoint

The `Booking` entity represents a reservation made by a user for a specific book. An endpoint for creating a booking is required to facilitate this functionality.

#### Booking Entity:

- **Fields**:
  - Book ID (Foreign Key)
  - User ID (Foreign Key) / Email (use email for simplicity Or set up a User table as a bonus)
  - Start Date
  - End Date
  - Status (e.g., active, cancelled)

#### Implementation Steps:


1. **Books List Endpoint**: Create a migration for the `Booking` entity to update the database schema.
 - **Define Route**: Create a route for the list  endpoint.
   - **Controller Logic**: Implement the logic to return records based on the request. This should include validation.
   - **Response**: Return a success status and the list if the operation is successful.


2. **Booking Creation Endpoint**:
   - **Define Route**: Create a route for the booking creation endpoint.
   - **Controller Logic**: Implement the logic to create a new booking record based on the request payload. This should include validation to ensure the book is available for the requested period.
   - **Response**: Return a success status and the booking details if the operation is successful.

3. **Booking Cancel Endpoint**:
    - **Define Route**: Create a route for the booking cancelation endpoint.
    - **Controller Logic**: Implement the logic to cancel a booking record based on the request payload. This should include validation to ensure the book is available for the requested period.
    - **Response**: Return a success status and the booking details if the operation is successful.

### MCD Details

#### Book Entity:

- **Title**: String
- **Description**: Long text
- **Author**: String
- **Publication Date**: DateTime
- **Category**: String

## Getting Started

Follow the instructions provided in each repository to set up the backend and frontend environments. Ensure all dependencies are installed and databases are migrated before starting the services.
