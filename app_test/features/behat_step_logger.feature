Feature: Behat steps logger
  In order to understand what happens behind the scene
  As dev
  I need to have log entries for each example/scenarios/steps executed

    @default-config
  Scenario: Assert default configuration
    Given extension step_logger config "enabled" is false

  Scenario: Assert custom configuration
    Given extension step_logger config "enabled" is true

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
