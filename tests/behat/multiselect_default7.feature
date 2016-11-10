@mod @mod_surveypro @javascript @_bug_phantomjs
Feature: Forms with a multi select field dependency
  In order to test multi select field dependency
  As an admin
  I need forms field which depends on multiple select options

  Scenario: Field should be enabled only when all select options are selected
    # Get to the fixture page.
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "activities" exist:
      | activity   | name | intro                                                                                       | course | idnumber |
      | label      | L1   | <a href="../mod/surveypro/tests/fixtures/multiselect_default7.php">Multiselect_default7</a> | C1     | label1   |
    And I log in as "admin"
    And I am on site homepage
    And I follow "Course 1"
    When I follow "Multiselect_default7"
# And I pause scenario execution
    Then the "Enter your name" "field" should be disabled