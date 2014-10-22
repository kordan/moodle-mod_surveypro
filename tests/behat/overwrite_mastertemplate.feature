@mod @mod_surveypro
Feature: verify the deletion of old items works as expected during master templates replacement
  In order to verify the overwrite of master templates
  As a teacher
  I need apply mastertemplates one over the previous

  @javascript
  Scenario: test that deletion of old items works as expected during master templates replacement
    Given the following "courses" exist:
      | fullname                 | shortname                | category | groupmode |
      | Overwrite mastertemplate | Overwrite mastertemplate | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | t        | teacher1@asd.com |
    And the following "course enrolments" exist:
      | user     | course                   | role           |
      | teacher1 | Overwrite mastertemplate | editingteacher |

    And I log in as "teacher1"
    And I follow "Overwrite mastertemplate"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
     | Surveypro name | To overwrite master template                      |
     | Description    | This is a surveypro to overwrite master templates |
    And I follow "To overwrite master template"

    And I set the field "mastertemplate" to "ATTLS (20 item version)"
    And I press "Create"
    Then I should see "Attitudes Towards Thinking and Learning"

    And I follow "Manage"
    And I press "Yes"

    And I follow "Master templates"
    And I follow "Apply"
    And I set the field "mastertemplate" to "COLLES (Actual Preferred)"
    And I press "Continue"
    Then I should see "I prefer that my learning focuses on issues that interest me."
    Then I should see "I found that my learning focuses on issues that interest me."

    And I follow "Manage"
    And I press "Yes"

    And I follow "Master templates"
    And I follow "Apply"
    And I set the field "mastertemplate" to "COLLES (Actual)"
    And I press "Continue"
    Then I should see "In this online unit I found that..."
    Then I should see "my learning focuses on issues that interest me"

    And I follow "Manage"
    And I press "Yes"

    And I follow "Master templates"
    And I follow "Apply"
    And I set the field "mastertemplate" to "COLLES (Preferred)"
    And I press "Continue"
    Then I should see "In this online unit, I prefer that..."
    Then I should see "my learning focuses on issues that interest me"

    And I follow "Manage"
    And I press "Yes"

    And I follow "Master templates"
    And I follow "Apply"
    And I set the field "mastertemplate" to "Critical Incidents"
    And I press "Continue"
    Then I should see "While thinking about recent events in this class, answer the questions below."
