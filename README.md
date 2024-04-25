moodle-mod_surveypro
====================

This module has been developed to address the need for highly validated data collection to gather customized information.
It was developed to solve data collection problems in the author's work environment.
What it was needed was, basically:
- an extremely controlled data collection.
- the ability to create web surveys quickly.
- a product that was adaptable as quickly as possible.
Surveypro plugin enables the creation of custom surveys by assembling fields and format elements.

Fields and formats, referred to as "elements" here, are two of the four managed plugins. Surveypro plugins include fields, formats, templates, and reports.

Surveypro comes with 22 built-in element plugins, comprising 18 field types and 4 format types. "Fields" are items needed by the student to submit responses, while "formats" are read-only elements like page breaks, fieldsets, or labels. If the desired field or format is not available, developers can be engaged to build a plugin that closely matches specific needs.

Here is a list of the 22 built-in elements with their intended use:
```
|--- field ---| --------------------------- Example of question ---------------------------- | -- Example of answer - |
| age         | How old were you when you started cycling?                                   | 4 years, 6 months      |
| autofill    | Autofill the response with infos from user, course, time, date or surveypro  | userid=528             |
| boolean     | Is it true?                                                                  | yes                    |
| character   | Write down your email, please                                                | thisisme@myserver.net  |
| checkbox    | What do you usually get for breakfast? milk, sugar, jam, chocolate, other... | milk, chocolate        |
| date        | When were you born?                                                          | October 12, 1492       |
| datetime    | Write down date and time of your last flight to Los Angeles                  | October 12, 2008 14:45 |
| fileupload  | Upload your CV in PDF format                                                 | myCV.pdf               |
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
```
"Fields" elements share common properties. Additionally, each field manages its specific features. Common properties include mandatory, indent, position of the element content, custom number, variable name, note, availability, validation, default and branching.

Less common properties include:
- Note: free text for the editing teacher to provide a custom explanation of an element.
- Availability: defines who will see the field in the form (all, hidden, reserved, search).
- Validation options: define the range of allowed responses for students.
- Default: predefined value displayed in the blank form.

User templates
--------------
A user template is a sub-plugin for editing teachers to quickly create snapshots of their surveys for sharing and later application.

Use cases for user templates include:
1) An editing teacher is frequently called to create custom surveys where a fixed set of elements is included. Let's say: First name, Last name, Date of birth, Gender. To save time, the editing teacher can create a surveypro including this set of elements and then save the corresponding user-template. Later, whenever he/she is called to create a new instance of surveypro with the set of elements of the first surveypro, he/she will apply the generated user-template to the new surveypro instance. All fields of the user-template will be included at once.
2) An editing teacher is asked to create the same surveypro in different courses. He can store a usertemplate at some level (category level, user level, or site level as last chance) and he/she will find it in each new course (of the same shared level) ready to be applied.
3) Two editing teachers are working on their local moodle instance drafting the same surveypro. Saving and sharing by email the corresponding usertemplate is a quick way to share progress achieved.

Master templates
----------------
Master template is the sub-plugin to quickly create a classic survey such as ATTLS, COLLES's or Critical Incidents.
Editing teacher is allowed to create his own master templates based on a manually created surveypro. The added value of master templates is the multilanguage behaviour. Once a course creator saves a surveypro as master template, in order to use it in a different moodle instance, he needs to copy it to the filesystem of the target moodle instance and to navigate the notification page. Each language added to the lang folder of the copied master template package will be available to students in the target moodle instance. This means that the same surveypro will be served to students in their own language, whether available.

Report
------
The report is a plugin for generating reports of gathered data, with a set of built-in reports available. The COLLES report manages graphs similar to the Moodle core survey module. Reports are pluggable, allowing for the development of custom reports by developers.

Branching
---------
Each element can be defined as a "child" of a different element based on a condition. Elements can have more than one child but only one parent through a simple "parent = answer" condition. This enables the creation of a surveypro where the availability of items depends on the answer to the first element.

Relational databases
--------------------
While Surveypro cannot strictly create a relational database, it provides a way to assist in this purpose. Editing teachers can include an autofill element to auto-answer information about the user, Moodle course, or surveypro instance. This enables the establishment of relations between data downloaded from different surveypro instances.
Let's suppose, for instance, that in the frame of a project, it is necessary to keep track of the disease history of patients through the collection of clinical information taken from swabs during the pandemic from covid19.
To achieve this goal, each user could be asked to complete two different instances of surveypro.
The first instance focused on personal data.
The second instance preposed to collect data on swabs taken during the pandemic from covid19.
If in both the first and second surveypro the surveypro designer includes an autofill type field that automatically adds the ID of the user submitting a response, once the responses collected by the two surveypros have been exported, it will be easy to create a relationship between them.

Groups
------
Privacy is maintained, ensuring that student responses are not displayed to other students. However, for groups repeatedly filling the same surveypro within the same research, each group member can see, edit, and delete responses from other group members. This is achieved by creating groups at the course or surveypro level and providing special permissions to students.

Features
--------
Several features are available at the surveypro instance level for different areas including:

**Develop**
- Develop new questions, formats, reports, or mastertemplates as sub-plugins of this activity.

**Design**
- Branches.
- Automatic page increment at branching.
- Assign an indent to questions to make parent-child relations clearer.
- Check the effectiveness of parent-child relations.
- Share in-progress surveypro definitions in XML with partners.
- Option to preserve history (allow the users to change their response BUT forcing them to save the modified response to a new one in order to preserve the history of what they did.).
- Automatic drop of abandoned responses.
- Keep "In progress" responses (do not drop "in progress" responses even if they should be, because of surveypro policies).
- Split a surveypro across multiple pages.
- Custom style sheet.
- Option for anonymous responses.
- Mandatory or optional questions.
- Notes (filling instructions) for each item.
- Format of short text items (email, URL, or custom regular expressions).
- Assign a name and custom number to each question.
- Tool to reorder items.
- Hide items still in progress or not ready to show.
- Custom thanks webpage.
- Tool to duplicate an item.
- Possibility to create relational databases once answers are exported.
- Limit the minimum and maximum year at surveypro level.
- Limit the input range of each item at the item level.
- Use "classic" surveys like COLLES, ATTLS, Critical Incidents.
- Have COLLES built using radio buttons or select items.
- Choose whether the label or value of checkboxes and selects should be in the response export file.
- Get positional answers of ticks in checkbox questions in the response export file.
- Mark items to include in the search form to search among submitted responses.
- Have a surveypro from a mastertemplate offering the multi-language feature.
- Open a period of risk in which "what should not be allowed" is allowed. To explain it better: a surveypro is online. Users started to submit their responses. I found an error. I need to correct it but... take care: if you edit an already submitted surveypro you risk to make a mess. What happens to already submitted answers if you add or drop an item? What happen to answers if you change the number or the content or the options of a select? Well it is really dangerous BUT if you know what you are doing and you really need to make changes (I did it only once in 10 years) you can ask to surveypro a "risky modification period" in which you are allowed to do what, generally, you shouldn't do.

**Submission**
- Set maximum attempts (limit the number of responses allowed to a user).
- Send reminder emails to enrolled users if they are late.
- Notify roles by sending user responses via email.
- Allow users to reject the answer to a specific question ("I don't answer").
- Allow users to pause surveypro submission to start again later.

**Management**
- Submitted responses can be modified, duplicated, deleted, searched and exported.
- Export of a single response in PDF format.
- Tool to export gathered responses.
- Tool to import new responses.
- Tool to create a custom user-template.
- Tool to create a custom master-template.
- Five built-in reports.

Contributing
------------
Contributions are welcome and can be made by:
- Using the plugin and reporting issues.
- Requesting reasonable new features.
- Fixing code and sharing solutions via pull requests on [GitHub](https://github.com/kordan/moodle-mod_surveypro/pulls).
- Developing tests and proposing improvements.
- Writing the user's manual.
- Contributing to translation.

I wrote this plugin to fulfill my personal needs. It does what I need by pandering exactly to my needs and following my way of thinking. It works well, very well, in everything I daily use. On the flip side, it may be riddled with errors in all the areas I have never used enough.
Also, I am an old programmer and the technology I use is never state-of-the-art no matter how hard I try to read and update.

For this reason, I believe the best contribution for this plugin is the addition of unit tests to best increase the percentage of verified code and the proposal of innovative changes to the code to increase its efficiency.

What is missing
---------------
- Unit tests.
- Extending the parent-child relation to more than a single parent (child is allowed if ( (parent1 = 1) or (parent2 = 0) ) or more complex relations (child is allowed if (parent1 > 10)).
- Compatibility with Moodle app for mobile.

License
-------
Surveypro is free software distributed under the terms of the GNU General Public License.
You can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

Surveypro is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have read a copy of the GNU General Public License along with Moodle. If not, see [Licence - GNU project - Free Software Foundation](http://www.gnu.org/licenses).

Report issue
------------
Please report issues about this module on [Surveypro on GitHub](https://github.com/kordan/moodle-mod_surveypro/issues).

Documentation
-------------
Refer to the [Moodle documentation](https://docs.moodle.org/dev/Survey_2_module) (incomplete and be outdated).

Known bugs
----------
- Failing server-side mform validation does not scroll to the first error.

**Fixed issues**
- (MDL-30940) Multiselect mform doesn't work.
- (MDL-34760, MDL-38975) How to disable fields upon checkboxes sets.
- (MDL-39280) $mform->disabledIf doesn't work for 'multiselect' mform elements.
- (MDL-45723) Uniformize the handling of SVGs in resolve_image_location() calls.

**Issues that are still a problem**
- (MDL-25067) mform editor element cannot be disabled with mform->disabledIf method.
- (MDL-56944) Style problem with file upload window.
- (MDL-61938) Failing server-side mform validation does not scroll to the first error using clean.
- (MDL-62634) Theme boost duplicates classes for checkbox mform elements.

**Issues that were not fixed but are no longer a problem**
- (MDL-29421) Form elements editor and file picker do not support freezing.
- (MDL-31796) $mform->disabledIf doesn't work for 'filemanager' mform elements.
- (MDL-45815) Filemanager mform elements are completely discarded in readonly forms.
- (MDL-43704) mform multiselect element is not disabled by the grouped checkbox.
- (MDL-28194) Trouble assigning styles to some mform elements.
- (MDL-34815) Disabled mform items need to be skipped during validation.
- (MDL-43689) With a set of advanced checkboxes, if the set is disabled each single checkbox returns its default value instead of its current value.
- (MDL-40418) A missing <label> tag in mform causes wrong display.
- (MDL-44138) The height of the same mform element changes with the content of other mform elements.
- (MDL-42946) It is not possible to provide a CSS style for a static mform element.
- (MDL-50739) Allow checkboxes to be styled even when frozen.
- (MDL-50740) Allow advcheckboxes to be styled even when frozen.
- (MDL-50741) Allow radiobuttons to be styled even when frozen.

Credits
-------
- Thanks to stronk7 for his continued support. His suggestions are milestones, his corrections are 100% reliable and his patience is unlimited. His involvement as "integrator" is one of the main quality assurance for this tool. His reflections are the main reason for the improvements of this moodle plugin.
- Thanks to Joseph Rézeau for his original text revision.
- Thanks to Germán Valero for the Spanish and Spanish-Mexican translations.
- Thanks to those who reported issues and provided feedback.
