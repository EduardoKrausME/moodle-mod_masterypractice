<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * Structure backup step.
 *
 * Learner adaptive state and Question Engine usages are intentionally not
 * copied. Duplicating an activity copies the learning design, not another
 * learner's evidence, schedule, or attempts.
 */
class backup_masterypractice_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines the backup tree.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $activity = new backup_nested_element('masterypractice', ['id'], [
            'name',
            'intro',
            'introformat',
            'scheduler',
            'questionspersession',
            'minquestions',
            'maxquestions',
            'estimatedminutes',
            'mixconcepts',
            'allowextra',
            'minsessioninterval',
            'mininterval',
            'maxinterval',
            'maxdailyreviews',
            'decayhalflifedays',
            'gradepolicy',
            'grade',
            'notifreview',
            'notifcooldown',
            'completionsessions',
            'completionquestions',
            'completionmastery',
            'completioncritical',
            'timecreated',
            'timemodified',
        ]);

        $concepts = new backup_nested_element('concepts');
        $categoryconcept = new backup_nested_element('categoryconcept', ['id'], [
            'sourceid',
            'sourcename',
            'includesubcategories',
            'weight',
            'critical',
            'criticalthreshold',
            'sortorder',
            'timecreated',
            'timemodified',
        ]);
        $tagconcept = new backup_nested_element('tagconcept', ['id'], [
            'sourceid',
            'sourcename',
            'weight',
            'critical',
            'criticalthreshold',
            'sortorder',
            'timecreated',
            'timemodified',
        ]);

        $activity->add_child($concepts);
        $concepts->add_child($categoryconcept);
        $concepts->add_child($tagconcept);

        $activity->set_source_table('masterypractice', ['id' => backup::VAR_ACTIVITYID]);

        $categoryconcept->set_source_sql(
            "SELECT c.*, qc.name AS sourcename
               FROM {masterypractice_concepts} c
               JOIN {question_categories} qc ON qc.id = c.sourceid
              WHERE c.masterypracticeid = ?
                AND c.sourcetype = ?",
            [
                backup::VAR_PARENTID,
                backup_helper::is_sqlparam('category'),
            ]
        );

        $tagconcept->set_source_sql(
            "SELECT c.*, t.rawname AS sourcename
               FROM {masterypractice_concepts} c
               JOIN {tag} t ON t.id = c.sourceid
              WHERE c.masterypracticeid = ?
                AND c.sourcetype = ?",
            [
                backup::VAR_PARENTID,
                backup_helper::is_sqlparam('tag'),
            ]
        );

        $categoryconcept->annotate_ids('question_category', 'sourceid');
        $activity->annotate_files('mod_masterypractice', 'intro', null);

        return $this->prepare_activity_structure($activity);
    }
}
