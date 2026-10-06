<?php
// Este arquivo faz parte do Moodle - http://moodle.org/

$string['pluginname'] = 'Mastery Practice';
$string['modulename'] = 'Mastery Practice';
$string['modulenameplural'] = 'Atividades Mastery Practice';
$string['pluginadministration'] = 'Administração do Mastery Practice';
$string['masterypracticename'] = 'Nome da atividade';

$string['masterypractice:addinstance'] = 'Adicionar uma atividade Mastery Practice';
$string['masterypractice:view'] = 'Visualizar Mastery Practice';
$string['masterypractice:attempt'] = 'Iniciar e concluir sessões de prática';
$string['masterypractice:viewreports'] = 'Visualizar relatórios do Mastery Practice';
$string['masterypractice:manageconcepts'] = 'Gerenciar conceitos do Mastery Practice';

$string['sessionheader'] = 'Sessões de prática';
$string['questionspersession'] = 'Questões por sessão';
$string['minquestions'] = 'Mínimo de questões';
$string['maxquestions'] = 'Máximo de questões';
$string['estimatedminutes'] = 'Duração estimada da sessão (minutos)';
$string['mixconcepts'] = 'Misturar conceitos na mesma sessão';
$string['allowextra'] = 'Permitir prática extra quando nada estiver vencido';
$string['minsessioninterval'] = 'Intervalo mínimo entre sessões';
$string['maxdailyreviews'] = 'Máximo de revisões de questões por dia';

$string['algorithmheader'] = 'Domínio e agendamento';
$string['scheduler'] = 'Estratégia';
$string['scheduler_leitner'] = 'Leitner';
$string['scheduler_sm2'] = 'SM-2';
$string['scheduler_adaptive'] = 'Adaptive Mastery';
$string['mininterval'] = 'Intervalo mínimo de revisão';
$string['maxinterval'] = 'Intervalo máximo de revisão';
$string['decayhalflifedays'] = 'Meia-vida do conhecimento (dias)';
$string['decayhalflifedays_help'] = 'Usada somente para estimar o domínio atual nas decisões de revisão e dashboards. Não reduz silenciosamente uma nota histórica nem remove a conclusão.';

$string['gradeheader'] = 'Nota';
$string['gradepolicy'] = 'Política de nota';
$string['gradepolicy_none'] = 'Sem nota';
$string['gradepolicy_best'] = 'Melhor sessão';
$string['gradepolicy_average'] = 'Média das sessões concluídas';
$string['gradepolicy_mastery'] = 'Domínio persistido';
$string['grademax'] = 'Nota máxima';
$string['masterygradeexplain'] = 'Nota e domínio são coisas diferentes. A nota é o resultado acadêmico enviado ao livro de notas; a estimativa de domínio atual pode diminuir depois por esquecimento sem alterar essa nota histórica.';

$string['notificationheader'] = 'Notificações de revisão';
$string['notifreview'] = 'Avisar quando uma revisão ficar disponível';
$string['notifcooldown'] = 'Tempo mínimo entre avisos de revisão';

$string['conceptsheader'] = 'Conceitos do Banco de Questões';
$string['conceptsconfiguredafter'] = 'Salve a atividade e use Gerenciar conceitos para escolher categorias e tags do Banco de Questões, definir pesos e marcar conceitos críticos.';
$string['manageconcepts'] = 'Gerenciar conceitos';
$string['addconcept'] = 'Adicionar conceito';
$string['editconcept'] = 'Editar conceito';
$string['deleteconcept'] = 'Excluir conceito';
$string['deleteconceptconfirm'] = 'Excluir este conceito e o estado de domínio derivado dos alunos?';
$string['concepttype'] = 'Tipo de origem';
$string['concepttype_category'] = 'Categoria de questões';
$string['concepttype_tag'] = 'Tag de questão';
$string['conceptsource'] = 'Origem no Banco de Questões';
$string['includesubcategories'] = 'Incluir subcategorias';
$string['conceptweight'] = 'Peso';
$string['criticalconcept'] = 'Conceito crítico';
$string['criticalthreshold'] = 'Limite de domínio crítico';
$string['noconcepts'] = 'Nenhum conceito foi configurado ainda.';
$string['invalidconceptsource'] = 'A origem selecionada não está disponível no Banco de Questões deste curso.';
$string['duplicateconcept'] = 'Este conceito já está configurado.';

$string['completionheader'] = 'Conclusão do Mastery Practice';
$string['completionsessions'] = 'Exigir sessões de prática concluídas';
$string['completionquestions'] = 'Exigir questões respondidas';
$string['completionmastery'] = 'Exigir domínio geral de pelo menos';
$string['completioncritical'] = 'Exigir que todo conceito crítico atinja seu limite';
$string['completiondetail:sessions'] = 'Concluir pelo menos {$a} sessões de prática';
$string['completiondetail:questions'] = 'Responder pelo menos {$a} questões';
$string['completiondetail:mastery'] = 'Atingir pelo menos {$a}% de domínio geral persistido';
$string['completiondetail:critical'] = 'Atingir o limite de todos os conceitos críticos';

$string['adminmininterval'] = 'Intervalo mínimo administrativo';
$string['adminmininterval_desc'] = 'Atividades não podem agendar revisões com frequência maior que esta.';
$string['adminmaxinterval'] = 'Intervalo máximo administrativo';
$string['adminmaxinterval_desc'] = 'Atividades não podem agendar revisões mais distantes que este limite.';
$string['adminmaxdailyreviews'] = 'Máximo administrativo de revisões diárias';
$string['adminmaxdailyreviews_desc'] = 'Máximo de revisões de questões que uma atividade pode permitir por aluno por dia.';
$string['difficultysamples'] = 'Amostra mínima para dificuldade';
$string['difficultysamples_desc'] = 'Até a questão atingir esta quantidade de observações na atividade, sua dificuldade permanece neutra.';

$string['domainmap'] = 'Seu mapa de domínio';
$string['currentmastery'] = 'Domínio atual estimado';
$string['persistedmastery'] = 'Domínio persistido';
$string['confidence'] = 'Confiança';
$string['nextreview'] = 'Próxima revisão';
$string['practicenow'] = 'Disponível agora';
$string['startpractice'] = 'Iniciar prática';
$string['continuesession'] = 'Continuar sessão atual';
$string['todaypractice'] = 'Prática de hoje';
$string['questioncount'] = '{$a} questões';
$string['estimatedtime'] = '≈ {$a} minutos';
$string['state_mastered'] = 'Dominado';
$string['state_progress'] = 'Em progresso';
$string['state_practice'] = 'Precisa praticar';
$string['state_overdue'] = 'Revisão vencida';
$string['state_unassessed'] = 'Ainda não avaliado';
$string['noquestionsavailable'] = 'Não existem questões avaliáveis automaticamente disponíveis para os conceitos configurados.';
$string['nothingdue'] = 'Nada está vencido agora. A próxima revisão recomendada é {$a}.';
$string['sessiontoosoon'] = 'Outra sessão poderá começar após {$a}.';
$string['dailylimitreached'] = 'O limite diário de revisões foi atingido.';
$string['sessioninprogress'] = 'Você já possui uma sessão de prática em andamento.';
$string['finishpractice'] = 'Concluir prática';
$string['sessioncompleted'] = 'Sessão concluída';
$string['score'] = 'Resultado da sessão';
$string['strengthened'] = 'Você fortaleceu';
$string['needsreview'] = 'Precisa revisar';
$string['recommendednext'] = 'Próxima prática recomendada';
$string['backtoactivity'] = 'Voltar para a atividade';
$string['evolution'] = 'Evolução do domínio';

$string['teacherdashboard'] = 'Dashboard do professor';
$string['classmastery'] = 'Domínio da turma';
$string['learnersattention'] = 'Alunos que podem precisar de atenção';
$string['indicatornote'] = 'Estes são indicadores pedagógicos baseados em participação e evidências de domínio, não julgamentos automáticos.';
$string['indicator_lowmastery'] = 'Domínio baixo';
$string['indicator_overdue'] = 'Revisões atrasadas';
$string['indicator_failures'] = 'Falhas consecutivas';
$string['indicator_lowparticipation'] = 'Baixa participação';
$string['usercount'] = 'Alunos';
$string['overduecount'] = 'Atrasados';
$string['lowcount'] = 'Domínio baixo';
$string['lastsummaryupdate'] = 'Resumo atualizado em: {$a}';
$string['summarynotready'] = 'Os resumos da turma ainda não foram gerados. Eles são atualizados por tarefa agendada.';
$string['viewdetails'] = 'Ver detalhes';
$string['conceptdetails'] = 'Detalhes do conceito';

$string['eventpracticesessionstarted'] = 'Sessão de prática iniciada';
$string['eventpracticesessioncompleted'] = 'Sessão de prática concluída';
$string['eventmasterylevelreached'] = 'Nível de domínio atingido';
$string['taskrebuildsummaries'] = 'Reconstruir resumos de turma do Mastery Practice';
$string['tasksendreviewnotifications'] = 'Enviar avisos de revisão do Mastery Practice';

$string['messageprovider:reviewavailable'] = 'Disponibilidade de revisão';
$string['reviewmessagesubject'] = 'Revisão disponível no Mastery Practice';
$string['reviewmessagebody'] = 'Existe uma revisão disponível em "{$a->activity}". Abra a atividade quando estiver pronto para praticar.';
$string['reviewmessagesmall'] = 'Existe uma revisão disponível no Mastery Practice: {$a}.';

$string['errorminmaxquestions'] = 'O mínimo de questões não pode ser maior que o máximo.';
$string['errorquestionspersession'] = 'A quantidade por sessão deve ficar entre o mínimo e o máximo configurados.';
$string['errorintervalorder'] = 'O intervalo mínimo deve ser menor ou igual ao intervalo máximo.';
$string['erroradminmininterval'] = 'O intervalo está abaixo do mínimo administrativo do site.';
$string['erroradminmaxinterval'] = 'O intervalo está acima do máximo administrativo do site.';
$string['erroradminmaxdaily'] = 'O limite diário está acima do máximo administrativo do site.';
$string['errorpercent'] = 'Informe um percentual entre 0 e 100.';
$string['errorpositive'] = 'Informe um valor maior que zero.';

$string['privacy:metadata:masterypractice_cstate'] = 'Armazena o estado de domínio do aluno para cada conceito configurado.';
$string['privacy:metadata:masterypractice_qstate'] = 'Armazena o agendamento de repetição espaçada por aluno e entrada do Banco de Questões.';
$string['privacy:metadata:masterypractice_sessions'] = 'Armazena resumos das sessões de prática do aluno.';
$string['privacy:metadata:masterypractice_squestions'] = 'Armazena quais versões de questões foram apresentadas e os sinais de resultado.';
$string['privacy:metadata:masterypractice_history'] = 'Armazena snapshots de domínio usados no histórico de evolução.';
$string['privacy:metadata:masterypractice_usummary'] = 'Armazena um resumo compacto do aluno na atividade.';
$string['privacy:metadata:userid'] = 'ID do usuário aluno.';
$string['privacy:metadata:mastery'] = 'Estimativa persistida de domínio.';
$string['privacy:metadata:confidence'] = 'Confiança na estimativa de domínio.';
$string['privacy:metadata:lastreview'] = 'Momento da última revisão.';
$string['privacy:metadata:nextreview'] = 'Momento agendado para a próxima revisão.';
$string['privacy:metadata:responsetime'] = 'Duração observada da resposta, usada como sinal auxiliar e em relatórios.';
$string['privacy:metadata:session'] = 'Dados da sessão de prática.';
$string['privacy:metadata:question'] = 'Identificadores do Banco de Questões e evidências de resultado de um item apresentado.';

$string['resetuserdata'] = 'Excluir dados dos alunos no Mastery Practice';
$string['indicator_noparticipation'] = 'Ainda sem participação';
$string['indicatorstitle'] = 'Indicadores';
$string['privacy:metadata:core_question'] = 'O Moodle Question Engine armazena as respostas enviadas durante as sessões de prática.';
$string['indicator_decline'] = 'Queda significativa recente de domínio';
