# Test Symfony

## Installation

1. Fork this repository
2. run in the project's root directory: `composer install`
3. Initialise the database. Update your connexion information in the app/config/parameters.yml file and run : 
`php app/console doctrine:database:create && php app/console doctrine:schema:create`
4. Run the command : `php app/console hl7adt:parse`
5. Use the `php app/console hl7adt:parse --help` command to know about the command's options.
 

