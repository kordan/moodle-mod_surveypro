@mod @mod_surveypro
Feature: verify multilang in mastertemplates
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
      | activity  | name                                     | intro         | course       | idnumber   | section |
      | surveypro | Multilang in ATTLS                       | To test ATTLS | ML Mtemplate | surveypro1 | 1       |
      | surveypro | Multilang in Colles Actual               | To test CA    | ML Mtemplate | surveypro2 | 2       |
      | surveypro | Multilang in Colles Preferred            | To test CP    | ML Mtemplate | surveypro3 | 3       |
      | surveypro | Multilang in Colles Preferred and Actual | To test CPA   | ML Mtemplate | surveypro4 | 4       |
      | surveypro | Multilang in Critical Incidents          | To test CI    | ML Mtemplate | surveypro5 | 5       |

  @javascript
  Scenario: display each mastertemplate in 2 different languages
    Given I log in as "admin"
    And I navigate to "Language > Language packs" in site administration
    And I set the field "menupack" to "Italiano"
    And I press "Install selected language pack(s)"
    Then I should see "Language pack 'it' was successfully installed"
    And I log out

    # Force English for UI.
    And I follow "English (en)" in the language menu
    And I log in as "teacher1"
    And I am on site homepage
    And I follow "Multilang mastertemplate"
    And I follow "Multilang in ATTLS"
    And I set the field "Master templates" to "ATTLS (20 item version)"
    And I press "Apply"
    Then I should see "Attitudes Towards Thinking and Learning"

    And I follow "Multilang in Colles Actual"
    And I set the field "Master templates" to "COLLES (Actual)"
    And I press "Apply"
    Then I should see "In this online unit"
    Then I should see "my learning focuses on issues that interest me"

    And I follow "Multilang in Colles Preferred"
    And I set the field "Master templates" to "COLLES (Preferred)"
    And I press "Apply"
    Then I should see "In this online unit"
    Then I should see "my learning focuses on issues that interest me"

    And I follow "Multilang in Colles Preferred and Actual"
    And I set the field "Master templates" to "COLLES (Preferred and Actual)"
    And I press "Apply"
    Then I should see "I prefer that my learning focuses on issues that interest me."
    Then I should see "I found that my learning focuses on issues that interest me."

    And I follow "Multilang in Critical Incidents"
    And I set the field "Master templates" to "Critical Incidents"
    And I press "Apply"
    Then I should see "While thinking about recent events in this class, answer the questions below."

    And I log out

    # Force Italiano for UI.
    And I follow "Italiano (it)" in the language menu
    # Take care: you are in Italian now and "Log in" has been replaced by "Login"
    And I follow "Login"
    And I set the following fields to these values:
      | Username | student1 |
      | Password | student1 |
    And I press "Login"

    And I am on site homepage
    And I follow "Multilang mastertemplate"
    When I follow "Multilang in ATTLS"
    And I press "Nuova risposta"
    Then I should see "Atteggiamenti nei Confronti del Pensare e dell'Imparare"

    And I follow "ML Mtemplate"
    And I follow "Multilang in Colles Actual"
    And I press "Nuova risposta"
    Then I should see "In questa unità online"
    Then I should see "il mio apprendimento si concentra sulle cose che mi interessano."

    And I follow "ML Mtemplate"
    And I follow "Multilang in Colles Preferred"
    And I press "Nuova risposta"
    Then I should see "In questa unità online"
    Then I should see "il mio apprendimento si concentra sulle cose che mi interessano."

    And I follow "ML Mtemplate"
    And I follow "Multilang in Colles Preferred and Actual"
    And I press "Nuova risposta"
    Then I should see "In questa unità online"
    Then I should see "Idealmente il mio apprendimento si concentra sulle cose che mi interessano."

    And I follow "ML Mtemplate"
    And I follow "Multilang in Critical Incidents"
    And I press "Nuova risposta"
    Then I should see "In classe in quale momento sei più partecipe come studente?"

    # Set again language to English to make "I log out" successfull.
    And I follow "English (en)" in the language menu

    And I log out
