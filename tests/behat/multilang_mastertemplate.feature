@mod @mod_surveypro
Feature: Test multilang in mastertemplates
  In order to verify mastertemplates display correctly in different languages // Why this feature is useful
  As a teacher and as a student                                               // It can be 'an admin', 'a teacher', 'a student', 'a guest', 'a user', 'a tests writer' and 'a developer'
  I display a mastertemplate                                                  // The feature we want

  Background:
    Given remote langimport tests are enabled
    And the following "courses" exist:
      | fullname                 | shortname    | category | groupmode |
      | Multilang mastertemplate | ML Mtemplate | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student   | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course       | role           |
      | teacher1 | ML Mtemplate | editingteacher |
      | student1 | ML Mtemplate | student        |
    And the following "activities" exist:
      | activity  | name                                     | intro         | course       | section |
      | surveypro | Multilang in ATTLS                       | To test ATTLS | ML Mtemplate | 1       |
      | surveypro | Multilang in Colles Actual               | To test CA    | ML Mtemplate | 2       |
      | surveypro | Multilang in Colles Preferred            | To test CP    | ML Mtemplate | 3       |
      | surveypro | Multilang in Colles Preferred and Actual | To test CPA   | ML Mtemplate | 4       |
      | surveypro | Multilang in Critical Incidents          | To test CI    | ML Mtemplate | 5       |

  @javascript
  Scenario: Display each mastertemplate in 2 different languages
    Given I log in as "admin"
    And I navigate to "Language > Language packs" in site administration
    And I set the field "menupack" to "Italiano"
    And I press "Install selected language pack(s)"
    Then I should see "Language pack 'it' was successfully installed"
    And I log out

    And I am on the "Multilang in ATTLS" "surveypro activity" page logged in as teacher1

    And I set the field "Master templates" to "ATTLS (20 item version)"
    And I press "Apply"
    Then I should see "Attitudes Towards Thinking and Learning"

    And I am on the "Multilang in Colles Actual" "surveypro activity" page
    And I set the field "Master templates" to "COLLES (Actual)"
    And I press "Apply"
    Then I should see "In this online unit"
    Then I should see "my learning focuses on issues that interest me"

    And I am on the "Multilang in Colles Preferred" "surveypro activity" page
    And I set the field "Master templates" to "COLLES (Preferred)"
    And I press "Apply"
    Then I should see "In this online unit"
    Then I should see "my learning focuses on issues that interest me"

    And I am on the "Multilang in Colles Preferred and Actual" "surveypro activity" page
    And I set the field "Master templates" to "COLLES (Preferred and Actual)"
    And I press "Apply"
    Then I should see "I prefer that my learning focuses on issues that interest me."
    Then I should see "I found that my learning focuses on issues that interest me."

    And I am on the "Multilang in Critical Incidents" "surveypro activity" page
    And I set the field "Master templates" to "Critical Incidents"
    And I press "Apply"
    Then I should see "While thinking about recent events in this class, answer the questions below."

    And I log out

    When I am on the "Multilang in ATTLS" "surveypro activity" page logged in as student1
    And I follow "Language" in the user menu
    And I follow "Italiano"
    And I press "Nuova risposta"
    Then I should see "Atteggiamenti nei Confronti del Pensare e dell'Imparare"

    And I am on the "Multilang in Colles Actual" "surveypro activity" page
    And I press "Nuova risposta"
    Then I should see "In questa unità online"
    Then I should see "il mio apprendimento si concentra sulle cose che mi interessano."

    And I am on the "Multilang in Colles Preferred" "surveypro activity" page
    And I press "Nuova risposta"
    Then I should see "In questa unità online"
    Then I should see "il mio apprendimento si concentra sulle cose che mi interessano."

    And I am on the "Multilang in Colles Preferred and Actual" "surveypro activity" page
    And I press "Nuova risposta"
    Then I should see "In questa unità online"
    Then I should see "Idealmente il mio apprendimento si concentra sulle cose che mi interessano."

    And I am on the "Multilang in Critical Incidents" "surveypro activity" page
    And I press "Nuova risposta"
    Then I should see "In classe in quale momento sei più partecipe come studente?"

    # Set again language to English to make "I log out" successfull.
    When I follow "Lingua" in the user menu
    And I follow "English"

    And I log out
