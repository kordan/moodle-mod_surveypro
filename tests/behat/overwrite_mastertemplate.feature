@mod @mod_surveypro
Feature: verify the deletion of old items works as expected during master templates replacement
  In order to verify the overwrite of master templates
  As a teacher
  I need apply mastertemplates one over the previous

  @javascript
  Scenario: deletion of old items works as expected when apply master templates
    Given the following "courses" exist:
      | fullname                 | shortname                | category | groupmode |
      | Overwrite mastertemplate | Overwrite mastertemplate | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | t        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                   | role           |
      | teacher1 | Overwrite mastertemplate | editingteacher |
    And the following "activities" exist:
      | activity  | name                          | intro                                | course                   |
      | surveypro | To overwrite master templates | To test overwrite of master template | Overwrite mastertemplate |
    And I am on the "To overwrite master templates" "surveypro activity" page logged in as teacher1

    When I select "More" from secondary navigation
    And I select "Master templates" from secondary navigation
    And I select "Apply" from the "jump" singleselect
    # And I am on the "To overwrite master templates" "mod_surveypro > Master templates apply" page
    And I set the field "mastertemplate" to "ATTLS (20 item version)"
    And I press "Apply"
    Then I should see "Attitudes Towards Thinking and Learning"

    When I select "Elements" from the "jump" singleselect
    And I press "Yes"

    And I am on the "To overwrite master templates" "mod_surveypro > Master templates apply" page
    And I set the field "mastertemplate" to "COLLES (Preferred and Actual)"
    And I press "Apply"
    Then I should see "I prefer that my learning focuses on issues that interest me."
    Then I should see "I found that my learning focuses on issues that interest me."

    When I select "Elements" from the "jump" singleselect
    And I press "Yes"

    And I am on the "To overwrite master templates" "mod_surveypro > Master templates apply" page
    And I set the field "mastertemplate" to "COLLES (Actual)"
    And I press "Apply"
    Then I should see "In this online unit"
    Then I should see "my learning focuses on issues that interest me"

    And I select "Elements" from the "jump" singleselect
    And I press "Yes"

    When I select "More" from secondary navigation
    And I select "Master templates" from secondary navigation
    And I select "Apply" from the "jump" singleselect
    # When I am on the "To overwrite master templates" "mod_surveypro > Master templates apply" page
    And I set the field "mastertemplate" to "COLLES (Preferred)"
    And I press "Apply"
    Then I should see "In this online unit"
    Then I should see "my learning focuses on issues that interest me"

    And I select "Elements" from the "jump" singleselect
    And I press "Yes"

    When I select "More" from secondary navigation
    And I select "Master templates" from secondary navigation
    And I select "Apply" from the "jump" singleselect
    # When I am on the "To overwrite master templates" "mod_surveypro > Master templates apply" page
    And I set the field "mastertemplate" to "Critical Incidents"
    And I press "Apply"
    Then I should see "While thinking about recent events in this class, answer the questions below."
