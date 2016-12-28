Feature: Behat steps logger
  In order to understand what happens behind the scene
  As dev
  I need to have log entries for each example/scenarios/steps executed

    @truncate-log-file @enable-behat-step-log-listener
  Scenario Outline: check logs entries
    Given A log entry must exist for current example start event
    Then I truncate log file
    And A log entry must exist for current step start event and I will have the one regarding end event
    Then I truncate log file
    And I will have a log entry regarding current example end event
    Examples:
    | var   |
    | value |
    | valu2 |
