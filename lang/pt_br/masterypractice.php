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

$string['addconcept'] = 'Adicionar conceito';
$string['adminmaxdailyreviews'] = 'Máximo administrativo de revisões diárias';
$string['adminmaxdailyreviews_desc'] = 'Máximo de revisões de questões que uma atividade pode permitir por aluno por dia.';
$string['adminmaxinterval'] = 'Intervalo máximo administrativo';
$string['adminmaxinterval_desc'] = 'Atividades não podem agendar revisões mais distantes que este limite.';
$string['adminmininterval'] = 'Intervalo mínimo administrativo';
$string['adminmininterval_desc'] = 'Atividades não podem agendar revisões com frequência maior que esta.';
$string['algorithmheader'] = 'Domínio e agendamento';
$string['allowextra'] = 'Permitir prática extra quando nada estiver vencido';
$string['backtoactivity'] = 'Voltar para a atividade';
$string['classmastery'] = 'Domínio da turma';
$string['completioncritical'] = 'Exigir que todo conceito crítico atinja seu limite';
$string['completiondetail:critical'] = 'Atingir o limite de todos os conceitos críticos';
$string['completiondetail:mastery'] = 'Atingir pelo menos {$a}% de domínio geral persistido';
$string['completiondetail:questions'] = 'Responder pelo menos {$a} questões';
$string['completiondetail:sessions'] = 'Concluir pelo menos {$a} sessões de prática';
$string['completionheader'] = 'Conclusão do Mastery Practice';
$string['completionmastery'] = 'Exigir domínio geral de pelo menos';
$string['completionquestions'] = 'Exigir questões respondidas';
$string['completionsessions'] = 'Exigir sessões de prática concluídas';
$string['conceptdetails'] = 'Detalhes do conceito';
$string['conceptsconfiguredafter'] = 'Salve a atividade e use Gerenciar conceitos para escolher categorias e tags do Banco de Questões, definir pesos e marcar conceitos críticos.';
$string['conceptsheader'] = 'Conceitos do Banco de Questões';
$string['conceptsource'] = 'Origem no Banco de Questões';
$string['concepttype'] = 'Tipo de origem';
$string['concepttype_category'] = 'Categoria de questões';
$string['concepttype_tag'] = 'Tag de questão';
$string['conceptweight'] = 'Peso';
$string['confidence'] = 'Confiança';
$string['continuesession'] = 'Continuar sessão atual';
$string['criticalconcept'] = 'Conceito crítico';
$string['criticalthreshold'] = 'Limite de domínio crítico';
$string['currentmastery'] = 'Domínio atual estimado';
$string['dailylimitreached'] = 'O limite diário de revisões foi atingido.';
$string['decayhalflifedays'] = 'Meia-vida do conhecimento (dias)';
$string['decayhalflifedays_help'] = 'Usada somente para estimar o domínio atual nas decisões de revisão e dashboards. Não reduz silenciosamente uma nota histórica nem remove a conclusão.';
$string['deleteconcept'] = 'Excluir conceito';
$string['deleteconceptconfirm'] = 'Excluir este conceito e o estado de domínio derivado dos alunos?';
$string['difficultysamples'] = 'Amostra mínima para dificuldade';
$string['difficultysamples_desc'] = 'Até a questão atingir esta quantidade de observações na atividade, sua dificuldade permanece neutra.';
$string['domainmap'] = 'Seu mapa de domínio';
$string['duplicateconcept'] = 'Este conceito já está configurado.';
$string['editconcept'] = 'Editar conceito';
$string['erroradminmaxdaily'] = 'O limite diário está acima do máximo administrativo do site.';
$string['erroradminmaxinterval'] = 'O intervalo está acima do máximo administrativo do site.';
$string['erroradminmininterval'] = 'O intervalo está abaixo do mínimo administrativo do site.';
$string['errorintervalorder'] = 'O intervalo mínimo deve ser menor ou igual ao intervalo máximo.';
$string['errorminmaxquestions'] = 'O mínimo de questões não pode ser maior que o máximo.';
$string['errorpercent'] = 'Informe um percentual entre 0 e 100.';
$string['errorpositive'] = 'Informe um valor maior que zero.';
$string['errorquestionspersession'] = 'A quantidade por sessão deve ficar entre o mínimo e o máximo configurados.';
$string['estimatedminutes'] = 'Duração estimada da sessão (minutos)';
$string['estimatedtime'] = '≈ {$a} minutos';
$string['eventmasterylevelreached'] = 'Nível de domínio atingido';
$string['eventpracticesessioncompleted'] = 'Sessão de prática concluída';
$string['eventpracticesessionstarted'] = 'Sessão de prática iniciada';
$string['evolution'] = 'Evolução do domínio';
$string['finishpractice'] = 'Concluir prática';
$string['gradeheader'] = 'Nota';
$string['grademax'] = 'Nota máxima';
$string['gradepolicy'] = 'Política de nota';
$string['gradepolicy_average'] = 'Média das sessões concluídas';
$string['gradepolicy_best'] = 'Melhor sessão';
$string['gradepolicy_mastery'] = 'Domínio persistido';
$string['gradepolicy_none'] = 'Sem nota';
$string['includesubcategories'] = 'Incluir subcategorias';
$string['indicator_decline'] = 'Queda significativa recente de domínio';
$string['indicator_failures'] = 'Falhas consecutivas';
$string['indicator_lowmastery'] = 'Domínio baixo';
$string['indicator_lowparticipation'] = 'Baixa participação';
$string['indicator_noparticipation'] = 'Ainda sem participação';
$string['indicator_overdue'] = 'Revisões atrasadas';
$string['indicatornote'] = 'Estes são indicadores pedagógicos baseados em participação e evidências de domínio, não julgamentos automáticos.';
$string['indicatorstitle'] = 'Indicadores';
$string['invalidconceptsource'] = 'A origem selecionada não está disponível no Banco de Questões deste curso.';
$string['lastsummaryupdate'] = 'Resumo atualizado em: {$a}';
$string['learnersattention'] = 'Alunos que podem precisar de atenção';
$string['lowcount'] = 'Domínio baixo';
$string['manageconcepts'] = 'Gerenciar conceitos';
$string['masterygradeexplain'] = 'Nota e domínio são coisas diferentes. A nota é o resultado acadêmico enviado ao livro de notas; a estimativa de domínio atual pode diminuir depois por esquecimento sem alterar essa nota histórica.';
$string['masterypractice:addinstance'] = 'Adicionar uma atividade Mastery Practice';
$string['masterypractice:attempt'] = 'Iniciar e concluir sessões de prática';
$string['masterypractice:manageconcepts'] = 'Gerenciar conceitos do Mastery Practice';
$string['masterypractice:view'] = 'Visualizar Mastery Practice';
$string['masterypractice:viewreports'] = 'Visualizar relatórios do Mastery Practice';
$string['masterypracticename'] = 'Nome da atividade';
$string['maxdailyreviews'] = 'Máximo de revisões de questões por dia';
$string['maxinterval'] = 'Intervalo máximo de revisão';
$string['maxquestions'] = 'Máximo de questões';
$string['messageprovider:reviewavailable'] = 'Disponibilidade de revisão';
$string['mininterval'] = 'Intervalo mínimo de revisão';
$string['minquestions'] = 'Mínimo de questões';
$string['minsessioninterval'] = 'Intervalo mínimo entre sessões';
$string['mixconcepts'] = 'Misturar conceitos na mesma sessão';
$string['modulename'] = 'Mastery Practice';
$string['modulenameplural'] = 'Atividades Mastery Practice';
$string['needsreview'] = 'Precisa revisar';
$string['nextreview'] = 'Próxima revisão';
$string['noconcepts'] = 'Nenhum conceito foi configurado ainda.';
$string['noquestionsavailable'] = 'Não existem questões avaliáveis automaticamente disponíveis para os conceitos configurados.';
$string['nothingdue'] = 'Nada está vencido agora. A próxima revisão recomendada é {$a}.';
$string['notifcooldown'] = 'Tempo mínimo entre avisos de revisão';
$string['notificationheader'] = 'Notificações de revisão';
$string['notifreview'] = 'Avisar quando uma revisão ficar disponível';
$string['overduecount'] = 'Atrasados';
$string['persistedmastery'] = 'Domínio persistido';
$string['pluginadministration'] = 'Administração do Mastery Practice';
$string['pluginname'] = 'Mastery Practice';
$string['practicenow'] = 'Disponível agora';
$string['privacy:metadata:confidence'] = 'Confiança na estimativa de domínio.';
$string['privacy:metadata:core_question'] = 'O Moodle Question Engine armazena as respostas enviadas durante as sessões de prática.';
$string['privacy:metadata:lastreview'] = 'Momento da última revisão.';
$string['privacy:metadata:mastery'] = 'Estimativa persistida de domínio.';
$string['privacy:metadata:masterypractice_cstate'] = 'Armazena o estado de domínio do aluno para cada conceito configurado.';
$string['privacy:metadata:masterypractice_history'] = 'Armazena snapshots de domínio usados no histórico de evolução.';
$string['privacy:metadata:masterypractice_qstate'] = 'Armazena o agendamento de repetição espaçada por aluno e entrada do Banco de Questões.';
$string['privacy:metadata:masterypractice_sessions'] = 'Armazena resumos das sessões de prática do aluno.';
$string['privacy:metadata:masterypractice_squestions'] = 'Armazena quais versões de questões foram apresentadas e os sinais de resultado.';
$string['privacy:metadata:masterypractice_usummary'] = 'Armazena um resumo compacto do aluno na atividade.';
$string['privacy:metadata:nextreview'] = 'Momento agendado para a próxima revisão.';
$string['privacy:metadata:question'] = 'Identificadores do Banco de Questões e evidências de resultado de um item apresentado.';
$string['privacy:metadata:responsetime'] = 'Duração observada da resposta, usada como sinal auxiliar e em relatórios.';
$string['privacy:metadata:session'] = 'Dados da sessão de prática.';
$string['privacy:metadata:userid'] = 'ID do usuário aluno.';
$string['questioncount'] = '{$a} questões';
$string['questionspersession'] = 'Questões por sessão';
$string['recommendednext'] = 'Próxima prática recomendada';
$string['resetuserdata'] = 'Excluir dados dos alunos no Mastery Practice';
$string['reviewmessagebody'] = 'Existe uma revisão disponível em "{$a->activity}". Abra a atividade quando estiver pronto para praticar.';
$string['reviewmessagesmall'] = 'Existe uma revisão disponível no Mastery Practice: {$a}.';
$string['reviewmessagesubject'] = 'Revisão disponível no Mastery Practice';
$string['scheduler'] = 'Estratégia';
$string['scheduler_adaptive'] = 'Adaptive Mastery';
$string['scheduler_leitner'] = 'Leitner';
$string['scheduler_sm2'] = 'SM-2';
$string['score'] = 'Resultado da sessão';
$string['sessioncompleted'] = 'Sessão concluída';
$string['sessionheader'] = 'Sessões de prática';
$string['sessioninprogress'] = 'Você já possui uma sessão de prática em andamento.';
$string['sessiontoosoon'] = 'Outra sessão poderá começar após {$a}.';
$string['startpractice'] = 'Iniciar prática';
$string['state_mastered'] = 'Dominado';
$string['state_overdue'] = 'Revisão vencida';
$string['state_practice'] = 'Precisa praticar';
$string['state_progress'] = 'Em progresso';
$string['state_unassessed'] = 'Ainda não avaliado';
$string['strengthened'] = 'Você fortaleceu';
$string['summarynotready'] = 'Os resumos da turma ainda não foram gerados. Eles são atualizados por tarefa agendada.';
$string['taskrebuildsummaries'] = 'Reconstruir resumos de turma do Mastery Practice';
$string['tasksendreviewnotifications'] = 'Enviar avisos de revisão do Mastery Practice';
$string['teacherdashboard'] = 'Dashboard do professor';
$string['todaypractice'] = 'Prática de hoje';
$string['usercount'] = 'Alunos';
$string['viewdetails'] = 'Ver detalhes';
