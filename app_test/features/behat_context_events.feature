 @enable-behat-step-event-listener
Feature: Behat steps events
  In order to understand what happens behind the scene
  As dev
  I need to catch events for each example/scenarios/steps executed

  Scenario Outline: check events example catch
    Given I listen for behat steps event
    And I should have caught event regarding current step start event and will have the end event
    Then I listen for behat steps event
    And I will catch event regarding current example end event
    Examples:
    | var   |
    | value |
    | valu2 |
