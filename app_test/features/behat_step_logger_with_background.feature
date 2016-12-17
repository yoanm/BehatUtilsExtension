Feature: Behat steps logger
  In order to understand what happens behind the scene
  As dev
  I need to have log entries for each backgrounds/scenarios/steps executed

  Background: check background logs entries
    Given A log entry must exist for current scenario start event
    Then I truncate log file
    And I will have a log entry regarding current background end event

    @custom-config @truncate-log-file @enable-behat-step-log-listener
  Scenario: check logs entry and event catch
     Given I truncate log file
     And A log entry must exist for current step start event and I will have the one regarding end event
     Then I truncate log file
     And I will have a log entry regarding current scenario end event
