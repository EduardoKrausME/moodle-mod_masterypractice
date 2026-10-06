<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * masterypractice.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['addconcept'] = 'Add concept';
$string['adminmaxdailyreviews'] = 'Administrative maximum daily reviews';
$string['adminmaxdailyreviews_desc'] = 'Maximum number of question reviews an activity may allow per learner per day.';
$string['adminmaxinterval'] = 'Administrative maximum review interval';
$string['adminmaxinterval_desc'] = 'Activity settings cannot schedule reviews farther apart than this.';
$string['adminmininterval'] = 'Administrative minimum review interval';
$string['adminmininterval_desc'] = 'Activity settings cannot schedule reviews more frequently than this.';
$string['algorithmheader'] = 'Mastery and scheduling';
$string['allowextra'] = 'Allow extra practice when nothing is due';
$string['backtoactivity'] = 'Back to activity';
$string['classmastery'] = 'Class mastery';
$string['completioncritical'] = 'Require every critical concept to meet its threshold';
$string['completiondetail:critical'] = 'Meet the threshold for every critical concept';
$string['completiondetail:mastery'] = 'Reach at least {$a}% overall persisted mastery';
$string['completiondetail:questions'] = 'Answer at least {$a} questions';
$string['completiondetail:sessions'] = 'Complete at least {$a} practice sessions';
$string['completionheader'] = 'Mastery Practice completion';
$string['completionmastery'] = 'Require overall mastery of at least';
$string['completionquestions'] = 'Require answered questions';
$string['completionsessions'] = 'Require completed practice sessions';
$string['conceptdetails'] = 'Concept details';
$string['conceptsconfiguredafter'] = 'Save the activity, then use Manage concepts to select Question Bank categories and tags, set weights, and mark critical concepts.';
$string['conceptsheader'] = 'Question Bank concepts';
$string['conceptsource'] = 'Question Bank source';
$string['concepttype'] = 'Source type';
$string['concepttype_category'] = 'Question category';
$string['concepttype_tag'] = 'Question tag';
$string['conceptweight'] = 'Weight';
$string['confidence'] = 'Confidence';
$string['continuesession'] = 'Continue current session';
$string['criticalconcept'] = 'Critical concept';
$string['criticalthreshold'] = 'Critical mastery threshold';
$string['currentmastery'] = 'Estimated current mastery';
$string['dailylimitreached'] = 'The daily review limit has been reached.';
$string['decayhalflifedays'] = 'Knowledge decay half-life (days)';
$string['decayhalflifedays_help'] = 'Used only to estimate current mastery for review decisions and dashboards. It never silently lowers a historical grade or removes completion.';
$string['deleteconcept'] = 'Delete concept';
$string['deleteconceptconfirm'] = 'Delete this concept and its derived learner mastery state?';
$string['difficultysamples'] = 'Question difficulty minimum sample';
$string['difficultysamples_desc'] = 'Until an item reaches this many observations in the activity, difficulty remains neutral.';
$string['domainmap'] = 'Your mastery map';
$string['duplicateconcept'] = 'This concept is already configured.';
$string['editconcept'] = 'Edit concept';
$string['erroradminmaxdaily'] = 'The daily review limit is above the site administrative maximum.';
$string['erroradminmaxinterval'] = 'The review interval is above the site administrative maximum.';
$string['erroradminmininterval'] = 'The review interval is below the site administrative minimum.';
$string['errorintervalorder'] = 'Minimum review interval must be smaller than or equal to the maximum interval.';
$string['errorminmaxquestions'] = 'Minimum questions cannot be greater than maximum questions.';
$string['errorpercent'] = 'Enter a percentage from 0 to 100.';
$string['errorpositive'] = 'Enter a value greater than zero.';
$string['errorquestionspersession'] = 'Questions per session must be between the configured minimum and maximum.';
$string['estimatedminutes'] = 'Estimated session duration (minutes)';
$string['estimatedtime'] = '≈ {$a} minutes';
$string['eventmasterylevelreached'] = 'Mastery level reached';
$string['eventpracticesessioncompleted'] = 'Practice session completed';
$string['eventpracticesessionstarted'] = 'Practice session started';
$string['evolution'] = 'Mastery evolution';
$string['finishpractice'] = 'Finish practice';
$string['gradeheader'] = 'Grade';
$string['grademax'] = 'Maximum grade';
$string['gradepolicy'] = 'Grade policy';
$string['gradepolicy_average'] = 'Average of completed sessions';
$string['gradepolicy_best'] = 'Best session';
$string['gradepolicy_mastery'] = 'Persisted mastery';
$string['gradepolicy_none'] = 'No grade';
$string['includesubcategories'] = 'Include subcategories';
$string['indicator_decline'] = 'Significant recent mastery decline';
$string['indicator_failures'] = 'Repeated failures';
$string['indicator_lowmastery'] = 'Low mastery';
$string['indicator_lowparticipation'] = 'Low participation';
$string['indicator_noparticipation'] = 'No participation yet';
$string['indicator_overdue'] = 'Overdue reviews';
$string['indicatornote'] = 'These are pedagogical indicators based on participation and mastery evidence, not automated judgements.';
$string['indicatorstitle'] = 'Indicators';
$string['invalidconceptsource'] = 'The selected source is not available in this course Question Bank.';
$string['lastsummaryupdate'] = 'Summary last updated: {$a}';
$string['learnersattention'] = 'Learners who may need attention';
$string['lowcount'] = 'Low mastery';
$string['manageconcepts'] = 'Manage concepts';
$string['masterygradeexplain'] = 'Grade and mastery are different. Grade is an academic result sent to the gradebook; current estimated mastery may later decrease because of knowledge decay without changing that historical grade.';
$string['masterypractice:addinstance'] = 'Add a Mastery Practice activity';
$string['masterypractice:attempt'] = 'Start and complete practice sessions';
$string['masterypractice:manageconcepts'] = 'Manage Mastery Practice concepts';
$string['masterypractice:view'] = 'View Mastery Practice';
$string['masterypractice:viewreports'] = 'View Mastery Practice reports';
$string['masterypracticename'] = 'Activity name';
$string['maxdailyreviews'] = 'Maximum question reviews per day';
$string['maxinterval'] = 'Maximum review interval';
$string['maxquestions'] = 'Maximum questions';
$string['messageprovider:reviewavailable'] = 'Review availability';
$string['mininterval'] = 'Minimum review interval';
$string['minquestions'] = 'Minimum questions';
$string['minsessioninterval'] = 'Minimum interval between sessions';
$string['mixconcepts'] = 'Mix concepts in the same session';
$string['modulename'] = 'Mastery Practice';
$string['modulenameplural'] = 'Mastery Practice activities';
$string['needsreview'] = 'Needs review';
$string['nextreview'] = 'Next review';
$string['noconcepts'] = 'No concepts have been configured yet.';
$string['noquestionsavailable'] = 'There are no automatically gradable Question Bank questions available for the configured concepts.';
$string['nothingdue'] = 'Nothing is due yet. The next recommended review is {$a}.';
$string['notifcooldown'] = 'Minimum time between review notifications';
$string['notificationheader'] = 'Review notifications';
$string['notifreview'] = 'Notify learners when a review becomes available';
$string['overduecount'] = 'Overdue';
$string['persistedmastery'] = 'Persisted mastery';
$string['pluginadministration'] = 'Mastery Practice administration';
$string['pluginname'] = 'Mastery Practice';
$string['practicenow'] = 'Available now';
$string['privacy:metadata:confidence'] = 'Confidence in the mastery estimate.';
$string['privacy:metadata:core_question'] = 'The Moodle Question Engine stores the responses submitted during Practice Sessions.';
$string['privacy:metadata:lastreview'] = 'Time of the last review.';
$string['privacy:metadata:mastery'] = 'Persisted mastery estimate.';
$string['privacy:metadata:masterypractice_cstate'] = 'Stores the learner\'s persisted mastery state for each configured concept.';
$string['privacy:metadata:masterypractice_history'] = 'Stores mastery snapshots used for learner progress history.';
$string['privacy:metadata:masterypractice_qstate'] = 'Stores spaced-review scheduling state for each learner and question bank entry.';
$string['privacy:metadata:masterypractice_sessions'] = 'Stores learner practice session summaries.';
$string['privacy:metadata:masterypractice_squestions'] = 'Stores which question versions were presented and the resulting score signals.';
$string['privacy:metadata:masterypractice_usummary'] = 'Stores a compact per-learner activity summary.';
$string['privacy:metadata:nextreview'] = 'Scheduled next review time.';
$string['privacy:metadata:question'] = 'Question Bank identifiers and result evidence for a presented item.';
$string['privacy:metadata:responsetime'] = 'Observed response duration used as an auxiliary signal and report value.';
$string['privacy:metadata:session'] = 'Practice session data.';
$string['privacy:metadata:userid'] = 'The learner user ID.';
$string['questioncount'] = '{$a} questions';
$string['questionspersession'] = 'Questions per session';
$string['recommendednext'] = 'Next recommended practice';
$string['resetuserdata'] = 'Delete Mastery Practice learner data';
$string['reviewmessagebody'] = 'A review is available in "{$a->activity}". Open the activity when you are ready to practise.';
$string['reviewmessagesmall'] = 'A Mastery Practice review is available in {$a}.';
$string['reviewmessagesubject'] = 'Mastery Practice review available';
$string['scheduler'] = 'Strategy';
$string['scheduler_adaptive'] = 'Adaptive Mastery';
$string['scheduler_leitner'] = 'Leitner';
$string['scheduler_sm2'] = 'SM-2';
$string['score'] = 'Session score';
$string['sessioncompleted'] = 'Session completed';
$string['sessionheader'] = 'Practice sessions';
$string['sessioninprogress'] = 'You already have a practice session in progress.';
$string['sessiontoosoon'] = 'Another practice session can start after {$a}.';
$string['startpractice'] = 'Start practice';
$string['state_mastered'] = 'Mastered';
$string['state_overdue'] = 'Review overdue';
$string['state_practice'] = 'Needs practice';
$string['state_progress'] = 'In progress';
$string['state_unassessed'] = 'Not yet assessed';
$string['strengthened'] = 'You strengthened';
$string['summarynotready'] = 'Class summaries have not been built yet. They are refreshed by scheduled task.';
$string['taskrebuildsummaries'] = 'Rebuild Mastery Practice class summaries';
$string['tasksendreviewnotifications'] = 'Send Mastery Practice review notifications';
$string['teacherdashboard'] = 'Teacher dashboard';
$string['todaypractice'] = 'Today\'s practice';
$string['usercount'] = 'Learners';
$string['viewdetails'] = 'View details';
