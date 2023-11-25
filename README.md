moodle-mod_surveypro
====================
This module comes from the need to perform a very closely validated data collection to gather customized data. It allows the creation of custom survey assembling fields and format elements.
Fields and formats, usually called "elements", are two of the four managed plugin. Surveypro plugins include: fields, formats, templates and report.

Surveypro ships with a set of 22 built-in elements as plugins. They include 18 field types and 4 format types. "Fields" are the user items needed by the student to enter their responses while "formats" are read-only elements like page break, fieldset or labels. If you can't find the field or the format you need, you can always ask a developer to build the plugin closely matching your needs.

This is a list of the 22 built-in elements with their supposed use.

|--- field ---| --------------------------- Example of question ---------------------------- | -- Example of answer - |
| age         | How old were you when you started cycling?                                   | 4 years, 6 months      |
| autofill    | Autofill the response with infos from user, course, time, date or surveypro  | userid=528             |
| boolean     | Is it true?                                                                  | yes                    |
| character   | Write down your email, please                                                | thisisme@myservet.net  |
| checkbox    | What do you usually get for breakfast? milk, sugar, jam, chocolate, other... | milk, chocolate        |
| date        | When were you born?                                                          | October 12, 1492       |
| datetime    | Write down date and time of your last flight to Los Angeles                  | October 12, 2008 14:45 |
| fileupload  | Upload your CV in PDF format                                                 | The file with your CV  |
| integer     | How many people does your family counts?                                     | 5                      |
| multiselect | What do you usually get for breakfast? milk, sugar, jam, chocolate, other... | jam, other=bread       |
| numeric     | What do you think is the ideal workplace temperature?                        | 25.5                   |
| radiobutton | Which summer holidays place do you prefer? sea, mountain, lake, hills        | sea                    |
| rate        | How confident are you with the following languages? EN, ES, IT, FR           | EN=2, ES=1, IT=3, FR=4 |
| recurrence  | When do you usually celebrate your name-day?                                 | October 12             |
| select      | Which summer holidays place do you prefer? sea, mountain, lake, hills        | lake                   |
| shortdate   | When did you buy your current car?                                           | October 2012           |
| textarea    | Write a short description of yourself                                        | This is me             |
| time        | At what time do you usually get up in the morning in a working day?          | 7:15                   |
|---------------------------------------------------------------------------------------------------------------------|

| -- format - | ------------------- Use ------------------ |
| label       | to display a message in the surveypro form |
| pagebreak   | to add a new page                          |
| fieldset    | to group your question                     |
| fieldsetend | to close an opened fieldset                |
|----------------------------------------------------------|

"Fields" elements share a set of properties. In addition to these, each field manages its own specific features. Among common properties, usually, can be found: mandatory, indent, position of the element content, custom number, variable name, note, availability, validation, default and branching.
The meaning of the less common properties is:
- Note is free text that the editing teacher can use to provide a custom explanation of an element.
- Availability defines who will see the field in the form. It can be:
-- all: each user will see the field in the survey form;
-- hidden: not any user will see the field in the survey form;
-- reserved: only students with specific permissions (accessreserveditems) will see the field in the survey form;
-- search: the field will be available in the search form too (if not hidden).
- Validation options define the range of allowed responses for the students.
- Default is what the blank form will display as predefined value of an element.
Help buttons in the module provide a quick explanation of each of those settings.

User templates
--------------
User template is the plugin for editing teachers to quickly make snapshots of their surveys to share and apply later on.
Case uses for user templates are, for instance,
1) An editing teacher is frequently called to create custom surveys where a fixed set of elements is included. Let's say: First name, Last name, Date of birth, Gender. To save time, the editing teacher can create a surveypro including this set of elements and then save the corresponding user-template. Later, whenever he/she is called to create a new instance of surveypro with the set of elements of the first surveypro, he/she will apply the generated user-template to the new instance. All fields of the user-template will be included at once.
2) An editing teacher is asked to create the same surveypro in different courses. He can store a usertemplate at some level (category level, user level, or site level as last chance) and he/she will find it in each new course (of the same shared level) ready to be applied.
3) Two editing teachers are working on their local moodle instance drafting the same surveypro. Saving and sharing by email the corresponding usertemplate is a quick way to share progress achieved.

Master templates
----------------
Templates are divided into two subcategories: user-templates and master-templates. Master template is the plugin to quickly create a classic survey such as ATTLS, COLLES's or Critical Incidents.
Editing teacher is allowed to create his own master templates based on a manually created survey. The added value of master templates is the multilanguage behaviour. Once a course creator saves a survey as master template, in order to use it in a different moodle instance, he needs to copy it to the filesystem of the target moodle instance and to navigate the notification page. Each language added to the lang folder of the master template package will be available to students in the target moodle instance. This means that the same survey will be served to students in their own language, whether available.

Report
------
This is the plugin to make report of the gathered data. A minimum set of builtin reports is available.
COLLES's report manages the same graphs as the current Moodle core survey module. Reports are pluggable so if you can't find the report matching your needs, you can always ask a developer to develop the one you really need and add it to your surveypro instance.

Branching
---------
Each element can be defined as "child" of a different element upon a condition. Each element can have more than one child, but can only have one parent through a simple "parent = answer" condition. This means that it is possible to create a three elements surveypro, for instance, where the availability of the last two items depends from the answer provided by the student to the first element. The first element, in this example, has two children while each child has only one parent.

Relational databases
--------------------
Surveypro can be used to create a relational database. Surveypro can not strictly create a relational database but gives a way to help in this purpose. Once an editing teacher creates a survey he/she can include an autofill element. This can be configured to auto-answer information about the user or the moodle course holding the surveypro instance or the surveypro instance itself. Let's imagine an editing teacher creates two surveypro instances. One asking for information about the living country of the student and one about habits of each of his/her family member. Once each surveypro is equipped with an autofill element providing the student userid it will be simple, once downloaded each gathered record, to make the 1 to n relation between the data taken from the two different surveypro. Surveypro is not relational but a relation can be established between data downloaded from different surveypro.

Groups
------
Usually the response provided by a student is NEVER displayed to the other students. All is covered by a kind of privacy. In spite of this, if a group of students is supposed to fill the same surveypro several times within the frame of the same research, each member of the group should be allowed to see, edit and, eventually, delete responses from each other member of his/her same group. This goal is reached making groups at course or surveypro level and providing special permissions (seeotherssubmissions, editotherssubmissions, deleteotherssubmissions) to students. Students of group A will never "see" the responses from students of group B but within the same group each student will be allowed to see/edit/delete the responses from people of his/her own group.

Features
--------
A few more features are available at instance level like:
Develop
    - develop your own new question, format, report or mastertemplate as they are sub-plugin of this activity;

Design
    - Branches;
    - Automatic page increment at branching;
    - Assign an indent to questions to to make simpler to understand parent-child relations;
    - Check the effectiveness of parent-child relations;
    - Share your in progress surveypro definition in XML with your partners;
    - Option to preserve history (allow the users to change their response BUT forcing them to save the modified response to a new one in order to preserve the history of what they do?);
    - Keep "In progress" (Do not drop "in progress" responses even if they should be dropped due to surveypro policies);
    - Custom style sheet;
    - Option for anonymous reponses;
    - Mandatory or optional questions;
    - Notes (filling instructions) to each item;
    - Format of short text items (Answer must be an email or url or satisfy a custom regulars expressions);
    - Assign a name to each question that will be reported in response export file;
    - Define a custom question number;
    - Tool to reorder items;
    - Hide items that are still in progress or that you still don't want to show in your surveypro;
    - Custom thanks web page;
    - Tool to duplicate an item;
    - Possibility to create a  relational databases once your answers are exported;
    - Limit the minimum and the maximum year at surveypro level in order to reduce typos at input time;
    - Limit the input range of each item at item level;
    - Use "classic" surveys like: COLLES, ATTLS, Critical Incidents;
    - Have COLLES built using radio buttons OR select items;
    - Automatic drop of abandoned responses;
    - Choose whether the label or value of checkboxes and select should be in the response export file;
    - Get positional answer of ticks in the checkbox questions in the response export file;
    - Mark items that are going to populate the form to search among submitted responses;
    - Have a surveypro from a mastertemplate offering the multi language feature. You see it in English and I see it in Italian even if we are accessing the same surveypro.
    - Open a period of risk in which "what should not be allowed" is allowed. To explain it better: a surveypro is online. Users started to submit their responses. I found an error. I need to correct it but... take care: if you edit an already submitted surveypro you risk to make a mess. What happens to already submitted answers if you add or drop an item? What happen to answers if you change the number or the content or the options of a select? Well it is really dangerous BUT if you know what you are doing and you really need to make changes (I did it only once in 10 years) you can ask to surveypro a "risky modification period" in which you are allowed to do what, generally, you shouldn't do.

Submission
    - Maximum attempts (Limit the number of responses allowed to a user);
    - Send to enrolled users a reminder email if they are late;
    - Notify role (Send the user response by email to users of a specific roles or to a specific email);
    - Allow the user to reject the answer to a specific question (The user responds: my answer is: "I don't answer");
    - Allow users to pause the surverpro submission to start it again later;

Use
    - Submitted responses can be modified, duplicated, deleted, searched and exported.

Contributing
------------
This tool was developed by me alone to solve data collection problems that had plagued my work environment for years.
We needed, basically, three things:
- extremely controlled data collection;
- the ability to create web surveys quickly;
- a product that we could adapt as quickly as possible.

Faced with the need to create a new tool, I chose to address problems in their generality rather than limit their solutions to my current problem. This, over time, allowed many third-party users to approach the product and benefit from it. Does this tool work? The most honest answer I can provide is: "I don't know". I can only assert with a very good margin of safety that it does exactly what I need but I have no idea about everything else.

I am not a teen ager since a long ime and dealing with and solving problems raised by new needs often takes me years because I have a thousand other interests and activities in my life.

If you find this tool meets your needs you might consider contributing to its grow.

How can you contribute?
- Use and report issues;
- request reasonable new features;
- fix code and share solutions via pull requests at https://github.com/kordan/moodle-mod_surveypro/pulls;
- write the user's manual: the hypothetical manual sections, "How do I get..." and "What uses does this tool allow?" have been asked a thousand times;

Licence
-------
Surveypro is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Surveypro is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have read a copy of the GNU General Public License
along with Moodle. If not, see <http://www.gnu.org/licenses/>.

Report issue
------------
Please report issues about this module in: https://github.com/kordan/moodle-mod_surveypro/issues

Documentation
-------------
https://docs.moodle.org/dev/Survey_2_module (very old and outdated)
(A group of people promised for a better documentation. Maybe...)

Known bugs
----------
- long text (using html editor) as child is not disabled by the parent even if in the same page:
see MDL-25067. I found it is caused by the js of the editor.

Fixed issues
------------
MDL-30940: multiselect mform doesn't work! Fixed by Frédéric Massart on April 19, 2013
MDL-34760, MDL-38975: How disable fields upon checkboxes sets? Fixed by Eloy Lafuente on April 8, 2013
MDL-39280: $mform->disabledIf doesn't work for 'multiselect' mform elements. Fixed by Frédéric Massart on May 10, 2013
MDL-45723: uniformize the handling of svgs in resolve_image_location() calls.

Issues that are still a problem
-------------------------------
MDL-25067: mform editor element can not be disabled with mform->disabledIf method
MDL-56944: style problem with file upload window
MDL-61938: Failing server side mform validation does not scroll to first error using clean
MDL-62634: theme boost duplicates classes for checkbox mform elements

Issues that were not fixed but that are no longer a problem
-----------------------------------------------------------
MDL-29421: Form elements editor and filepicker do not support freezing. [Fixed locally by overriding corresponding class]
MDL-31796: $mform->disabledIf doesn't work for 'filemanager' mform elements.
[Fixed along the years witout any notification in this tracker issue. Today (May 2015) it seems to work as expected]
MDL-45815: filemanager mform elements are completely discarded in readonly forms.
[Fixed locally by overriding corresponding class]
MDL-43704: mform multiselect element is not disabled by the grouped checkbox (as many of other mform elements do)
[Fixed with the workaround described in the tracker]
MDL-28194: I am in trouble assigning syles to some mform element.
MDL-34815: Disabled mform items need to be skipped during validation (and more).
MDL-43689: With set of advanced checkboxes, if the set is disabled each single chechbox returns its default value instead of its current value
MDL-40418: A missing <label> tag in mform causes wrong display
MDL-44138: The height (in pixel) of the same mform element changes with the content of the other mform elements
MDL-42946: It is not possible to provide a css style for a static mform element. [Fixed locally by overriding corresponding class]
MDL-50739: Allow checkboxes to be styled even when frozen
MDL-50740: Allow advcheckboxes to be styled even when frozen
MDL-50741: Allow radiobuttons to be styled even when frozen

Credits
-------
Thanks to stronk7 for his continued support. His suggestions are milestones, his corrections are 100% reliable and his patience is unlimited. His involvement as "integrator" is one of the main quality assurance for this tool. His reflections are the main reason for the improvements of this moodle plugin.
Thanks to Joseph Rézeau for the revision of the text and the English.
Thanks to Germán Valero for the translation into Spanish and Spanish-Mexican.
Thanks to those who contacted me to report an issue or just to ask how to move.
