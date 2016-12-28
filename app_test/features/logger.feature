Feature: Logger
  In order log something
  As behat context
  I need to have logger injected

  Scenario: Logger
    Given I have access to a logger
    And I truncate log file
    When I log a test message
    Then Test message is in log file
