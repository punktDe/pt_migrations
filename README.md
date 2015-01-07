pt_migrations
=============

## Installation

1. Install the extension as usual in TYPO3 

2. Copy the file `Configuration.sample.yaml` to `config/Configuration.yaml` where config is on the same level as your document root.

3. Double check that `migrations_directory` contains valid relative paths from the document root to your folders which contain the migrations.

## Running commands

Run `TYPO3_CONTEXT=current_context ./migrate migrations:cmd` in bin folder.

`current_context` has to be the context node name from your configuration file.
F.e. `Development/Vagrant`.
`cmd` can currently be `status`, `migrate` or `version`.

## Show migration status

Run `TYPO3_CONTEXT=current_context ./migrate migrations:status` in bin folder.

## How to run a migration

Run `TYPO3_CONTEXT=current_context ./migrate migrations:migrate` in bin folder.

## How to add or delete version of migration table

Run `TYPO3_CONTEXT=current_context ./migrate migrations:version` in bin folder.

## Further resources

- [Introduction to doctrine](http://docs.doctrine-project.org/projects/doctrine-migrations/en/latest/reference/introduction.html)
