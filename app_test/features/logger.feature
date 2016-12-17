Feature: Logger
  In order log something
  As behat context
  I need to have logger injected

    @custom-config @default-config
  Scenario: Logger
    Given I have access to a logger
    And I truncate log file
    When I log a test message
    Then Test message is in log file

    @default-config
  Scenario: Assert default configuration
    Given extension logger config "path" is "behat.log"
    And extension logger config "level" is 200

    @custom-config
  Scenario: Assert custom configuration
    Given extension logger config "path" is "behat2.log"
    And extension logger config "level" is 100
