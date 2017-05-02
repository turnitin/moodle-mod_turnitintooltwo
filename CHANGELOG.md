### Date:		2017-March-13
### Release:	v2017031301

- Minor update Gradebook method refactor so it can be called from other contexts.
- Extended the character limit for class and assignment titles.
- Added a warning if PHP SOAP extension is not installed.
- Implemented a 'force refresh button' to assignment settings page.
- Resolved an issue affecting revealer's name in anonymous submissions.
- Stopped re-enrolling previously un-enrolled Moodle students.
- Fixed a bug with anonymity on newly created assignment parts.
- Resolved bug affecting assignment inbox access.
- Fixed an issue causing all submissions to refresh upon individual student login.


**Extended the character limit for class and assignment titles** - Some users found the character limit for class and assignment titles restrictive; we've extended the title limits to 256 characters, allowing more freedom when setting up classes and assignments.

**Added a warning if PHP SOAP extension is not installed** - We found that installation would fail without PHP SOAP (PHP5-SOAP, PHP7-SOAP) being installed, and although it's a required package, this wasn't listed as being so. We've added a warning in settings if the extension is not installed, saving you from a failed installation. 

**Implemented a 'force refresh button' to assignment settings page** - Some users informed us that grades were failing to appear in the grade book after their insertion in the document viewer, as well as submission updates not transferring to the assignment inbox. To fix this, we've added a button allowing you to force refresh this data, syncing all submission changes to the grade book and inbox.

**Resolved an issue affecting revealer's name in anonymous submissions** - Reports revealed that the student's name appeared as the revealer of an anonymous submission, rather than the name of the instructor. This resulted in Turnitin administrators having no record of the instructor who requested the reveal. This has now been rectified.

**Stopped re-enrolling previously unenrolled Moodle students** - It appeared that unenrolled students would be re-enrolled when instructors downloaded submissions from Turnitin. We've fixed this! Now, when a student who has submitted is removed from a Moodle course, they will not be re-enrolled when the submission is refreshed.

**Fixed a bug with anonymity on newly created assignment parts** - We received reports that when a second (or more parts) were added to a single-part anonymous assignment, the anonymity setting was not passed across. Our engineers have fixed this problem and our testers have ensured that new parts of an anonymous assignment are anonymous. Thanks for your patience with this one!

**Resolved bug affecting assignment inbox access** - If a student had two submissions for one assignment part in Turnitin, the Moodle assignment inbox would continue to load and hang with the error message: Loading data from Turnitin. We resolved this by ensuring that the system only saves one submission record per student per assignment when refreshing submissions from Turnitin.

**Fixed an issue causing all submissions to refresh upon individual student login** - If a student logged into the assignment inbox, all submissions were refreshed, causing students to show as having updated the grade for other graded submission(s) and subsequently appear as graders in grade reports. We've resolved this by ensuring that a student only has an effect on their own submission.


---

### Date:		2017-February-22
### Release:	v2017022201

- Verified against Moodle 3.2.
- UI modified for compatibility with Boost theme.
- First unit tests added to plugin.

---

### Date:		2017-January-25
### Release:	v2017012501

- This release and future releases will no longer work on Moodle 2.6.
- The language strings have been updated across all supported languages.
- The implementation of submission deletion has been changed so that it is no longer a link.
- Data dump display has been optimised to help with large databases.
- If the required fileinfo extension is missing it will now be mentioned on the plugin settings page.
- The plugin will now not make excessive calls to Turnitin when using the bulk enrolment tool.
- Suspended users now no longer appear in the assignment inbox.
- Non-submitter e-mails will no longer be sent to inactive students. (Thanks to @junwan6)
- Travis-CI has been aded to the plugin as an extra QA resource to help flag any issues with the code.
- Inclusion paths were consolidated for the ./sdk directory. (Thanks to @eviweb)
- Fixes:
	- Fixed an issue with duplicate submission rows showing in the Moodle database.
	- Fixed an issue with anonymous marking where the overall grade would sometimes not appear.
	- The assignment edit API call no longer fails if repository settings don't match the plugin settings.
	- The submission processing message that was missing in Moodle 3.1 is now visible again.
	- Fixed a bug where the submission inbox would not load if an enrolled student has been deleted in Turnitin.
	- Characters that cannot be used in a file name in Windows are now removed from submission titles to prevent submission errors.

---

### Date:		2016-December-21
### Release:	v2016122101

- Fixes:
	- Changed the language codes to correctly recognise Simplified Chinese in Moodle.
	- Pass the correct Simplified Chinese language code to Turnitin.

---

### Date:		2016-September-14
### Release:	v2016091401

- Remove leftover counter variable from get_submission_inbox function.
- Refactored the get users functionality in several places to not include users with roles inherited from site level.
- Fixes:
	- Add missing string to email non submitters.
	- Check $CFG exists before using in version.php (Thanks to @micaherne).
	- Assignments starting more than a year ago can now be restored without user info.
	- Check for GradeMark feedback changes when saving submission.
	- Only show GradeMark launch to student if the paper has feedback or grade.
	- Slight tweak to overall grade to show overall grade on normal assignments.

---

### Date: 		2016-July-26
### Release:	v2016072601

- Verified against Moodle 3.1
- Changed display of names to be consistent with Moodle (Thanks to junwan6).
- Improved the way grade updates are handled for entering grades to the gradebook.
- Fixes:
	- Ignore inherited roles when sending instructor notifications.
	- Scheduled tasks problems with deleting classes from database where no entry exists in course_modules table.
	- Replace a couple of missing icons in plugin configuration area.
	- Grade related settings are now hidden if GradeMark is not enabled.

---

### Date:       2016-April-11
### Release:    v2016011105

- Added support form to contact Tii support directly from the plugin.
- Datatables styling now specific to Turnitin tables to avoid conflict.
- Activity logs for submissions are now more informative.
- Created warning on config page for the customer to check whether translated matching and ETS are configured at account level
- Fixes:
	- Fixed issue with postdate in anonymous marking mode - the page now warns the user the impact on anonymity of moving the post-date.
	- Enrolls user if necessary when performing a course restoration.
	- Fixed cron logic to prevent multiple assignment creation upon cron event failure.

---

### Date:       2016-February-23
### Release:    v2016011104

- Grade display help text is now wrapped.
- Links to migration tool have been renamed to course restoration.
- Block JavaScipt code has been moved out of the direct package as part of our efforts to separate plugins.
- Cron functionality has been moved to scheduled tasks (Thanks to mwehr).
- Allow emails to be sent from the noreply address.
- Fixes:
	- Fixed an issue where the file name would be appending multiple times if the temp file can't be created.
	- Fixed cron warning message regarding REQUEST_URI (Thanks to AviMoto).
	- Query fixed during cron for Postgres (Thanks to mwehr).

---

### Date:       2016-January-25
### Release:    v2016011102

- Fixes:
	- Reworked DV launchers to remove cross domain iframe problem preventing opening in Safari.

---

### Date:       2016-January-12
### Release:    v2016011101

- Instructors to be notified when a submission has been made.
- Contrast changed for inbox error messages.
- Check added to verify if php_mbstring is enabled.
- Ensure filename to be sent to Turnitin is UTF-8 encoded
- Unnecessary addition of user being updated in Turnitin and submission inbox being viewed removed from plugin activity logs.
- Unused (pre Moodle 2.6) $module settings removed from version.php.
- Moodle's cron will update OR scores if necessary after the due date has passed.
- Use default values if user has no firstname or lastname.
- Fixes:
	- Anonymous grades to gradebook after post date has passed processed by cron.
	- Grademark icon visibility now dependent on whether GradeMark feedback exists rather than a grade.
    - Multi-part assignment Delete icon missing from "delete parts".
    - Table sorting images shown in Submission inbox.
    - Fix object variable in ajax request for PHP7 compatability.
    - Additional user status checking added to view.php functions (Thanks to Skylar Kelty)
	- Replace deprecated mime_content_type function in submission to Turnitin process.

---

### Date:       2015-November-30
### Release:    v2015040111

- Verified against Moodle 3.0
- Javascript is now minified.
- Grades can now be shown as a percentage.
- Datatables has been updated to the latest version.
- Added a note to highlight the 24 hour Originality Report delay for resubmissions.
- Fixes:
	- The assignment part displayed is now remembered upon submission deletion.
	- TII user record is removed if Moodle user does not exist when unlinked.
	- Fixed potential difference in temp directory path.
	- Fixed an issue where the Rubric view link is not visible for students.
	- Fixed an issue where the Grademark icon was not clickable if resubmissions are enabled.
	- Shared rubrics is now initialised when creating a Turnitin class.
	- Fixed an issue where course restore was grabbing the wrong class.
	- Student first name default is now saved in the plugin settings when student privacy is enabled.
	- Fixed an issue where student names are visible in the file name when student privacy is enabled.
	- An issue where the EULA modal would not open properly when certain themes are being used.

---

### Date:       2015-October-01
### Release:    v2015040110

- Unnecessary setting of course removed from view.php.
- Grade category selectable for Turnitin Assignments.
- Allow HTML in disclaimer message.
- Notice added to warn assignment creators to check against sources.
- Fixes:
	- Download bulk files adheres to checked submissions.
	- Anonymous marking inconsistencies when resetting assignment.
	- File titles cleaned up before creating temp files to remove slash permission errors.
	- Assignment edit error occuring on user enrolment when retrieving grades.
	- Correct upload limit shows for students.
	- Messages inbox loads in correct modal rather than new window.

---

### Date:       2015-September-16
### Release:    v2015040109

- API URL for UK institutions automatically changed to api.turnitinuk.com.
- Shared Turnitin Rubrics can be attached to assignments.
- Digital receipts can be sent without SMTP settings enabled.
- Icons replaced with Font Awesome and Tii font sets.
- Modified logging on submission upload and deletion.
- Ability added to send a message to user who not yet submitted to an assignment.
- Fixes:
	- Rubric Manager now shows Shared Rubrics.
	- Submission modal resizing errors and EULA acceptance loop fixed as EULA has moved from submission modal to submission inbox
	- Extra user role check added on submission deletion.
	- File check added before sending to Turnitin.
	- Submissions are now correctly linked when backing up and restoring (Thanks to Adam Olley).
	- File check added and slashes removed from filename before sending to Turnitin.

---

### Date:       2015-July-31
### Release:    v2015040107

- Verified against Moodle 2.9
- Fixes:
	- Account for Shared Rubrics being returned by the API.

---

Releases before version 2015040106 will refer to changes made to the Turnitin's other Moodle plugins as well; the plagiarism plugin and block.

### Date:       2015-June-29
### Release:    v2015040106

- Increase submission limit to Turnitin to 40Mb for newly created classes.
- Show Rubric to Plagiarism plugin students before submission if applicable.
- Update User code reinstated to update user's details in Turnitin.
- Entry to Moodle logs added for a blank grading template submission.
- Fixes:
	- Export options no longer available once post date has passed for earliest assignment part.
	- Change status codes for submissions made on Dan Marsden's previous plugin.
	- Sorting by title no longer sorts on paper id.
	- Selecting no grading type hides marks in Turnitin Assignment inbox.
	- Deleted Moodle users are now accounted for when saving submission data.
	- On attempting to restore a course, if the owner doesn't exist then it is reassigned to site admin. (Thanks to daparker26).
	- Special characters that were causing errors removed from submission titles.
	- Remove the large amounts of user data stored in user session in Turnitin Assignment.
	- Avoid endless loops if error occurs on creating a temp file. (Thanks to Jonathon Fowler).
	- Turnitin Assignments now inaccessible through URL if access is restricted.
	- The correct attempt is now graded in Plagiarism plugin.
	- Unsigned integers changed to signed on the install database script.
	- Log text reworded when a student views the inbox.
	- Temporary files are now removed correctly in the Plagiarism plugin. (Thanks to Dan Marsden).
	- Resubmission warning no longer showing after due date.
	- Gradelib file included in Turnitin Assignment cron.

---

### Date:       2015-June-11
### Release:    v2015040105

- Plagiarism plugin support for marking workflow.
- Logging added for resubmissions.
- Fixes:
	- Several database queries fixed to offer full Oracle and SQL Server support.
	- Course end date modal box fixed in Course Migration Tool.
	- Empty submission successful message no longer shown for unsuccessful submisisons.
	- Manual user enrolment to courses with existing Turnitin Assignments fixed.
	- Files added in Moodle Assignment settings no longer submitted to Turnitin.
	- Import to course no longer creates a new Turnitin class if Turnitin Assignments already exist.
	- Users enrolled on class in Turnitin if they are not active users on account.

---

### Date:       2015-May-19
### Release:    v2015040104

- Unused code and unused legacy events removed.
- EULA can be declined in a PP assignment with submissions then only processed by Moodle and not sent to Turnitin.
- New exception handlers added to PP cron. (Thanks to Jeff Kerzner).
- Allow plugin installation without configuration data. (Thanks to Chris Wharton).
- Display all option added to unlink users table.
- PP config code refactored to use Moodle config functions. (Thanks to Michael Hughes).
- Submission deleted box added.
- Tidying up of Turnitin Assignment inbox.
- A digital receipt is now sent to a student when a submission is made to Turnitin (if SMTP is setup in Moodle).
- Fixes:
	- Files removed from PP submission are no longer included in average grade calculation. (Thanks to Tony Butler).
	- Document Viewer no longer hangs in Safari.
	- Undefined offsets on my homepage removed.
	- Submit paper link misalignment.
	- Undefined text on Quickmark Manager closing link.
	- Unlink users refactored to remove unnecessary connection to Turnitin.
	- PP Text content resubmissions no longer sent if there is no content.
	- Refresh submission links shown after refreshing of parts.
	- Part id being set incorrectly for multi-part assignments when refreshing updated submissions in Moodle.

---

### Date:       2015-April-15
### Release:    v2015040102

- Fixes:
	- Fix continuous test connection that was impacting PP EULA.

---

### Date:       2015-April-01
### Release:    v2015040101

- Inputting API URL is now actioned via a select box.
- Old files removed from files table in Plagiarism plugin if no longer part of a submission.
- Updating part names in inbox edits the part tab straightaway.
- Turnitin connection can be tested without having to save first.
- Student can now view digital receipt now from inbox.
- Anonymous marking explanation added to Plagiarism plugin settings.
- Test connection call in Plagiarism plugin cron changed to be static.
- Index on submission_objectid added to turnitintooltwo_submissions table.
- Locks added to Plagiarism plugin defaults. (Thanks to Brendan Heywood).
- Select all option added to Turnitin Assignment inbox.
- Fixes:
	- Modals reworked to use embedded template and handle Turnitin errors without showing theme.
	- Help text corrected in Turnitin Assignment.
	- Account Id is trimmed when saved in configuration.
	- File downloads through settings area.
	- Updating module name in course page no longer creates duplicate event.
	- Course participation report in 2.6 no longer throws error.
	- Anonymous Marking close box closes. (Thanks to Dr. Joseph Baxter).
	- Incorrect variable name in Settings changed. (Thanks to Trevor Cunningham).
	- Pending OR scores no longer launch DV.
	- Instructors can submit to a Turnitin Assignment after the due date.
	- Include paths consiolidated. (Thanks to eviweb).
	- If disclaimer is enabled, then the student can not click submit until they have checked the disclaimer.
	- Only allow Plagiarism plugin modules to have a due date one year ahead when created in Turnitin.
	- Unnecessary PeerMark refreshing removed and print_overview reworked. (Thanks to Dr. Joseph Baxter).
	- Overall grades not displayed to students until last post date has passed.
	- When DV closes in Plagiarism plugin and Turnitin Assignment, all modified grades are updated.
	- Anonymous marking can no longer be turned on and off if a submission has been made.
	- User given warning when attempting to move post date on an Anonymous marking assignment.
	- Spinner added when refreshing submissions in Turnitin Assignment.
	- Refresh submissions button added to Plagiarism plugin settings.
	- Empty resubmission can no longer be sent.

---

### Date:       2015-February-23
### Release:    v2014012413

- Block split into separate github repository.
- EULA modal window resized in Turnitin Assignment.
- Close banner added to modal windows.
- Index created on externalid in plagiarism_turnitin_files table.
- Uploaded files renamed to include useful information.
- Use Grademark config setting used as main grademark setting rather than by assignment.
- Papers transferred in Turnitin are now accounted for when refreshing individual submissions.
- Administrators can now specify whether assignments always go to Standard or No Repository.
- Fixes:
	- Voice comments are now recordable in Safari.
	- Database installer fixed for Moodle 2.3. (Thanks to Jeff Kerzner)
	- Cron request to update submissions now performed in batches. (Thanks to Jeff Kerzner)
	- Help text wrapping inconsistency on Turnitin assignment settings page.
	- Editing dates in Turnitin Assignment inbox accounts for environments with set time zones. (Thanks to NeillM)
	- Page URLs changed to proper URLs. (Thanks to Matt Gibson and Skylar Kelty)
	- Validation added so that part names must be unique.
	- Plagiarism plugin now works with blog and single forum types.

---

### Date:       2015-January-29
### Release:    v2014012412

- Moodle event logging added for Turnitin Assignments.
- Submission title in Turnitin Assignment inbox now opens the Document viewer.
- Group submissions are now partially supported in the Plagiarism plugin. There are limitations with being able to display the Turnitin document viewer for text content submissions, particularly from the default group.
- Fixes:
	- Pop-ups within Document viewer no longer blocked.
	- Plugin upgrade check hidden in admin search results.
	- Filenames are shortened to less than 200 characters before being sent to Turnitin.
	- PP Class reset query fixed for Postgres databases.
	- Export options no longer hidden when viewing Turnitin Assignments with Anonymous Marking enabled.
	- Cron in PP no longer checks for similarity scores where a report is not expected.
	- All students in a group submission to a Moodle assignments can now see the similarity score.
	- Grades for group submissions to Moodle assignments are now applied to all students in the group.
	- Overall grade is now updated in the gradebook if a part is deleted from a multi-part Turnitin Assignment.
	- Moodle exception thrown if non admin user accesses unlink users page. (Thanks to Dr Joseph Baxter)
	- Grademark links no longer shown in PP if Moodle assignment is not to be graded.
	- Grade item entry no longer checked for if Moodle assignment is not to be graded.
	- Check for Turnitin connection before checking EULA acceptance in PP. (Thanks to Tony Butler)
	- Sort by submission date corrected in Turnitin Assignment.
	- PP enable checkboxes removed from Moodle 2.3 as only assignment is available.
	- PP submission area decluttered when Javascript is not enabled.
	- Grademark warning for non submitting users now shows on subsequent clicks.
    - Reset PP submission error code and msg when file successfully submitted.

---

### Date:       2014-November-28
### Release:    v2014012411

- Performance logging of curl calls (provided by Androgogic).
- Fixes:
	- Turnitin Assignment inbox can now be sorted by similarity score and grade.
	- Hard errors changed to soft errors when the PP cron is run.
	- Instructors no longer override other instructors rubrics in PP.
	- If a PP submission has been attempted 5 times and errors each time it will be removed from the queue.
	- Multiple attempts are handled properly - except text content where previous attempts can not be viewed.
	- Incorrect grade calculation (Null grades from previous submissions no longer included) fixed in PP.
	- DV Window resizable.
	- Print original submission from DV Window.

---

### Date:       2014-November-17
### Release:    v2014012410

- Cron scores update in the Plagiarism plugin are now split by submission type.
- Fixes:
	- Anonymous marking reveal form fixed and now initialises correctly on inbox load.
	- Incorrect repository value fixed when synching assignments in Plagiarism plugin.
	- Assignment title length check added on Turnitin assignments.
	- Resubmission grade warning no longer shown when resubmission is not possible.
	- Post date stored correctly for PP assignment (Thanks to Michael Aherne).
	- Post dates not updated for future PP assignments.
	- DV opening fixed for Moodle 2.3.

---

### Date:       2014-October-08
### Release:    v2014012409

- Czech language pack added.
- Plagiarism plugin now uses the hidden until date from gradebook as the post date on Turnitin.
- PP Post date in Turnitin is now stored in Moodle.
- Connection test added to cron event handler.
- Unnecessary Gradebook update removed when viewing Turnitin Assignment.
- Specify assign when looking for user's grades in PP (Thanks to mattgibson).
- PHP end tags removed to fit with moodle guidelines.
- PHP header function replaced with moodle redirect function to fit with moodle guidelines.
- Error handling added when getting users for tutors and students tabs.
- Error handling added when enrolling all students in tutors and students tabs.
- Submissions are removed from the events cron if a student has not accepted the EULA.
- EULA is now presented via an Iframe rather than a separate tab.
- Late submissions allowed setting in Turnitin for Plagiarism plugin assignments is now always true.
- Fixes:
	- Details for a non moodle user who is only in expired classes can be retrieved when grabbing submission data.
	- Logger class renamed in SDK.
	- Gradelib file included in cron.
	- Scope of tool tipster anti-aliasing fixed to not affect whole of Moodle.
	- Date of late submissions indicated in red.
	- Oracle database error when getting forum post.
	- Inbox hidden columns fixed if Grademark is disabled.
	- Individual part post dates can now be the same as post date.
	- Submissiontype now used in correct context in PP file errors.
	- Test connection now hidden on plugin upgrade.
	- Incorrect word count on text content submissions fixed.
	- Moodle assignment due dates now advanced by 1 day in Turnitin instead of 1 month.
	- Select all checkbox fixed in Unlink users screen.
	- Editable date boxes now re-enable after esc is pressed while one is active.
	- Document viewer no longer hangs in Safari and is no longer blocked by popups.
	- Student can delete a submission that hasn’t gone to Turnitin in a Turnitin assignment.

---

### Date:       2014-September-22
### Release:    v2014012408

- Fixes:
	- EULA notice removed from PP submissions with previous submissions.
	- Rubrics now being saved in PP.
	- EULA no longer blocked by popups in Turnitin Assignment.
	- EULA & Disclosure no longer being shown if PP is disabled for module (Thanks to Dan Marsden).

---

### Date:       2014-September-04
### Release:    v2014012407

- Remove Grademark settings if GradeMark is disabled. (Thanks to Alex Rowe)
- Date handling reconfigured in PP to prevent erros (Thanks to Dan Marsden)
- Fixes:
	- File errors page no longer errors if file has been deleted. (Thanks to Ruslin Kabalin)
	- Course migration bug no longer tries to populate PP array in migration if PP not installed.
	- Inbox submission links now work after refreshing non moodle users submissions in Turnitin Assignment.
	- Assignment Grade (PP) table no longer populated if grade is null when cron runs.
	- Encoding issue with module description fixed.
	- Anonymous marking no longer set if not enabled in settings (Thanks to Dan Marsden).

---

### Date:       2014-August-19
### Release:    v2014012406

- Error reporting added for files that are too large, small submissions and any other submission errors.
- Error reporting added to cron.
- Error reporting and success statement added at submission stage.
- Non acceptance of EULA now indicated to tutor in inbox.
- Error indicators and rollover messages now displayed in inbox.
- Error messages saved and displayed in settings area.
- EULA moved to submission declaration and submission form hidden.
- Turnitin Paper Id now shown next to submission to show that paper has been submitted.
- Fixes:
	- Long assignment titles are now truncated.
	- Link to a file in Assignment Summary now renders correctly.
	- Inbox part date editing now works on Windows servers.
	- Cron in PP changed to check for scores when ORcapable is 1.
	- Course Migration query fixed when creating class.
	- Course migration error fixed when no Turnitin courses to link to exist.

---

### Date:       2014-June-11
### Release:    v2014012405

- Course reset functionality added to remove Turnitin data when a class/module is reset.
- Ability added to enable/disable Turnitin in individual modules.
- Ability added for instructors to refresh individual rows in a Turnitin Assignment.
- Automatic grade refreshing from Turnitin can now be turned off in Turnitin Assignments.
- Anonymous marking and Translated matching settings removed in PP modules if they are disabled in config.
- Config warning added if plugin has not been configured.
- Anonymous marking option is locked once a submission is made to any assignment part.
- Font Awesome added to plugin
- EULA closing reworked to accomodate IE
- Javascript cleaned up in block to use Moodle value (Thanks to Skylar Kelty).
- Version file updated for Moodle 2.7+ compatibility (Thanks to Skylar Kelty).
- Javascript reorganised to fit better with Moodle guidelines
- Erroneous debugging removed (Thanks to Skylar Kelty).
- Check for XMLWriter extension added to settings area.
- Removed restriction on word count and content length if accepting any file type in PP.
- Removed restriction in PP to allow submissions after the due date.
- Automatic connection test and upgrade check in settings stopped and changed to buttons.
- User creation removed from restore procedure.
- Additonal indexes added to database tables
- Extra permission checks added for migration tool
- Error message now shown if ajax request to get submissions times out
- Improved CSS to scope only to plugins and files added to jQuery plugin organisation
- Forum posts are now submitted to Turnitin when posted
- Database dump added to PP settings page
- WSDL files used by SDK are now stored locally.
- SDK setting added to use Moodle SSL certificate if it is present.
- Code changes as required by Moodlerooms to better fit Moodle guidelines
- Fixes:
	- User could submit to Turnitin Assignment without accepting Moodle disclaimer
	- Postgres type error when searching unlinked users query
	- A grade set to 0 in GradeMark was showing as — in Turnitin Assignment
	- Allow Non OR file type setting now being changed in Turnitin
	- New file submissions with same filename display correct OR link in PP.
	- Peermark Manager now accessible to any instructor in PP
	- Turnitin Messages Inbox now accessible to any instructor
	- Gradebook now updates when post date is changed on the inbox screen.
	- Grademark null grades no longer overwrite grades previously set in Moodle via PP.
	- Accept anything setting is now passed to recreated assignment in Course migration
	- Feedback files no longer sent to Turnitin in PP
	- Admin now enrolled on class when migrating incase they are not on the account.
	- PP cron now ignores files with no OR score when cron attempts to refresh scores.
	- Grades now removed from Gradebook when submission is deleted.

---

### Date:       2014-June-11
### Release:    v2014012404

- EULA acceptance is now stored locally for submissions.

---

### Date:       2014-April-17
### Release:    v2014012403

- Grademark link removed for student if a grade has not been set in Plagiarism Plugin.
- Feedback release date changed on forum with plagiarism plugin to be the same as start date.
- Infinite loading of Document viewer stopped.
- Full Catalan language pack added.
- Submissions in Plagiarism plugin stopped if there has been 5 unsuccessful attempts.
- Link removed for Originality Report if there is no score.
- Fixes:
	- Incorrect links to GradeMark and Originality Report for students have been hidden.
	- Conflicts with Bootstrap theme for tooltips and fixed grademark link position.
	- Incorrect settings link in the Plagiarism plugin.
	- Timestamp was being incorrectly set preventing more than 1 batch of submissions updating from Turnitin.
	- Student is now enrolled on the class when checking EULA acceptance to ensure they are on account.

---

### Date:       2014-February-26
### Release:    v2014012402

- Vietnamese Language pack added.
- Option to send draft submissions to Turnitin in Plagiarism Plugin reinstated.
- Diagnostic mode reinstated to disable logging by default.
- Troubleshooting documentation expanded.
- Fixes:
	- Student’s who’d never submitted could not view rubric, they’re now enrolled at this point.
	- Instructor now being enrolled in course when resetting to prevent errors in reading memberships.
	- OR Link was being shown in Plagiarism Plugin for non OR submissions.
	- Submissions now processed in Plagiarism Plugin if due date disabled.
	- Rubric List was not being populated in Plagiarism Plugin settings.
	- Updating of OR scores depending on OR submissions capability fixed in Plagiarism Plugin.
	- Cut off date / late submission issues solved in Plagiarism Plugin (Thanks to Chris Wharton).
	- Generic CSS issues fixed that were breaking some user’s themes.
	- Timezone was not being accounted for when editing part dates in inbox.
	- Editing title in course context is now updated in Turnitin.
	- Submit nothing link removed if submission has been made to Moodle but not yet processed by Turnitin
	- Incorrect grade scale calculation.
	- Previous Turnitin users were not being joined to account on Plagiarism plugin.

---

### Date:       2014-January-24
### Release:    v2014012401

- File type limit removed.
- Ability to accept no file added so that marks / grades can be allocated to non file submissions
- Dependencies added to plagiarism plugin and blocks
- Fixes:
	- Error occurring in course reset

---

### Date: 		2013-December-18
### Release:	v2013121801

- Supports Turnitin Originality Checking, GradeMark and PeerMark
- Allows access to the Rubric Manager and Quickmark Manager from within the Moodle environment
- Supports multi-part assignments allowing draft and revision submissions
- Allows instructors to submit work on behalf of students
- Supports Moodle Grade Scales and updates the Moodle gradebook with grades entered in GradeMark
- Supports Moodle Groups
- Allows multiple instructors to access a class and assignments in Turnitin’s web interface
- Supports Moodle’s built-in plagiarism detection thereby allowing access to Turnitin functionality from within Moodle assignments
- Incorporates a Class Migration feature allowing access to classes and assignments that are in Turnitin but not in the Moodle environment
