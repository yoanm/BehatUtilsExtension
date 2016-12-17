 @default-config @enable-behat-step-event-listener
Feature: Behat steps events
  In order to understand what happens behind the scene
  As dev
  I need to catch events for each backgrounds/scenarios/steps executed

  Background: check background events catch
    Given I should have caught event regarding current scenario start event
    Then I listen for behat steps event
    Then I will catch event regarding current background end event

  Scenario: check logs entry and event catch
     Given I listen for behat steps event
     And I should have caught event regarding current step start event and will have the end event
     Then I listen for behat steps event
     And I will catch event regarding current scenario end event
