# Homegrown(MV)C - Models

### Requirements
HomegrownMVC's models are written to work with PDO, so you can use any database
that has PDO drivers.

### Model Types
HomegrownMVC's models are an abstraction of collections and individual elements.
  * PluralModel: Runs queries which return collections of SingularModels
  * SingularModel: Calls operations in PluralModel to instantiate individual
    elements
    
### Creating Models
Both PluralModel and SingularModel are abstract classes.

TODO
