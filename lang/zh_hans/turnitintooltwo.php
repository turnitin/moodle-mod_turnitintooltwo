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

// Version: 2015040101
// Date: 2015-04-01
// Language: zh_hans.

$string['addsubmission'] = '添加提交内容';
$string['allowlate'] = '允许在截止日期后提交';
$string['allsubmissions'] = '提交收件箱';
$string['anon'] = '匿名';
$string['anonenabled'] = '已启用匿名标记';
$string['anytype'] = '任何提交类型';
$string['assigngeterror'] = '无法获得 turnitintooltwo 数据';
$string['ced'] = '课程结束日期';
$string['classcreationerror'] = 'Turnitin 课程创建失败';
$string['classupdateerror'] = '无法更新 Turnitin 课程数据';
$string['configureerror'] = '您必须完全以管理员身份配置此单元才能在课程内使用它。请联系您的 Moodle 管理员。';
$string['connecttest'] = '测试 Turnitin 连接';
$string['connecttestcommerror'] = '无法连线至 Turnitin。请再次检查您的 API URL 设置。';
$string['connecttesterror'] = '连线至 Turnitin 时出错。错误回覆讯息如下：<br />';
$string['connecttestsuccess'] = 'Moodle 已成功地连线至 Turnitin。';
$string['copyrightagreement'] = '一旦勾选此框格，我确认此提交是我自己的作品。我接受所有可能因此提交而造成的侵权的责任。';
$string['coursegeterror'] = '无法获得课程数据';
$string['courseiderror'] = '课程代号错误';
$string['deleteconfirm'] = '是否确定要删除此提交内容？\n\n此操作无法撤消。';
$string['deletesubmission'] = '删除提交内容';
$string['downloadsubmission'] = '下载提交内容';
$string['dtdue'] = '截止日期';
$string['dtpost'] = '公布日期';
$string['dtstart'] = '开始日期';
$string['excludebiblio'] = '不含参考书目';
$string['excludebiblio_help'] = '此设置允许指导教师选择排除在学生论文内的参考书目、引述的作品，或参考文献内出现的内文，使其在生成原创性报告时不会被检查。此设置可以在各个原创性报告内撤消。';
$string['excludepercent'] = '百分比';
$string['excludequoted'] = '排除引用资料';
$string['excludequoted_help'] = '在生成原创性报告时，此设置允许导师选择排除引述的文字，使其不被检查。此设置可以在各个原创性报告内撤消。';
$string['excludevalue'] = '排除小型匹配结果';
$string['excludevalue_help'] = '此设置允许指导教师在生成原创性报告时选择排除长度不够长的相符处（由指导教师设置）使其不被考虑。此设置可以在各个原创性报告内撤消。';
$string['excludewords'] = '字';
$string['filetosubmit'] = '要提交的文件';
$string['filetosubmit_help'] = '当将文件提交至某个作业部分，请浏览您的计算机以找到您要使用此格式上传的文件。';
$string['fileupload'] = '文件上传';
$string['genduedate'] = '在截止日期当天生成报告（允许在截止日期前重新提交）';
$string['genimmediately1'] = '立即生成报告（不允许重新提交）';
$string['genimmediately2'] = '立即生成报告（允许在截止日期前重新提交）';
$string['institutionalrepository'] = '机构存储库（适用时）';
$string['institutionalchecksettings'] = '与机构存储库<br />做比较';
$string['institutionalcheck'] = '与机构存储库做比较';
$string['internetcheck'] = '与网络比较';
$string['internetcheck_help'] = '当为论文处理原创性报告时与 Turnitin 网络存储库比较。如果这没有被选择的话，相似处指数百分比可能下降。';
$string['journalcheck'] = '与杂志、<br />期刊与刊物比较';
$string['journalcheck_help'] = '当为论文处理原创性报告时与 Turnitin 杂誌、期刊与刊物存储库比较。如果这没有被选择的话，相似处指数百分比可能下降。';
$string['maxfilesize'] = '文件大小上限';
$string['maxfilesize_help'] = '此设置决定了用户提交至每个作业部分的内容的文件大小上限。您可以设置的最大值由课程设置中设置的值决定，而此值受制于 40Mb 这一文件大小上限，也就是允许上传至 Turnitin 的内容的文件大小上限。';
$string['maxlength'] = '{$a->field} 的最大长度为 {$a->length} 个字符';
$string['maxmarks'] = '最高分数';
$string['pluginname'] = 'Turnitin 作业 2';
$string['modulename'] = 'Turnitin 作业 2';
$string['modulenameplural'] = 'Turnitin 作业';
$string['moduleversion'] = '版本';
$string['mysubmissions'] = '我的提交';
$string['nolimit'] = '无限制';
$string['nonmoodleuser'] = '非 Moodle 用户';
$string['norepository'] = '无存储库';
$string['nosubmissions'] = '尚未提交';
$string['notavailableyet'] = '不可用';
$string['numberofparts'] = '部分的总数';
$string['numberofparts_help'] = '允许创建包含多部分的作业，每个用户可以提交作平至每一部分。';
$string['overallgrade'] = '整体成绩';
$string['overallgrade_help'] = '总体分数决定总体作业所允许的成绩上限。作业的每一部分将会被分配到一部分的成绩，然后按照比例总和以计算总体分数。';
$string['partdberror'] = '输入第 {$a} 部分至数据库时出错<br />';
$string['partupdateerror'] = '更新数据库中的第 {$a} 部分时出错<br />';
$string['partdeleteerror'] = '无法删除第 {$a} 作业部分数据';
$string['partdeletewarning'] = '您尝试删除的作业部分包含提交内容。如果您删除此作业部分，您将会丢失这些提交内容。n\n\是否确定要继续？';
$string['partposterror'] = '截止日期必须在公布日期前。';
$string['partgeterror'] = '无法获取作业部分数据';
$string['partname'] = '作业部分';
$string['partnameerror'] = '部分名称不可为空白。';
$string['partdueerror'] = '截止日期必须在公布日期前。';
$string['pending'] = '未决';
$string['permissiondeniederror'] = '您尚未获得正确的许可以完成所请求的动作';
$string['pluginadministration'] = 'Turnitin 作业 2 管理';
$string['portfolio'] = '组合夹';
$string['print'] = '打印';
$string['proxypassword'] = 'Proxy 密码';
$string['proxypassword_desc'] = '<b>[可选]</b><br />若您的 proxy 需要验证，请在此输入密码。';
$string['proxyport'] = 'Proxy 槽';
$string['proxyport_desc'] = '<b>[可选]</b><br />若您的服务器使用 Proxy 來连结网络，请在此输入 proxy 槽。';
$string['proxyurl'] = 'Proxy URL';
$string['proxyurl_desc'] = '<b>[可选]</b><br />若您的服务器使用 Proxy 來连结网络，请在此输入 proxy 地址。';
$string['proxyuser'] = 'Proxy 用户名称';
$string['proxyuser_desc'] = '<b>[可选]</b><br />若您的 proxy 需要验证，请在此输入用户名。';
$string['reportgenspeed'] = '报告生成速度';
$string['resubmission'] = '重新提交';
$string['resubmissiongradewarn'] = '在截止日期前皆可以重新提交。如果论文被重新提交，任何成绩都将会被删除。您想要继续吗？';
$string['resubmissiongradewarnaware'] = '请注意，如果进行此重新提交，则将删除所有标记。';
$string['resubmit'] = '重新提交';
$string['reveal'] = '显示';
$string['revealerror'] = '若要显示学生姓名，您必须要有正当的理由';
$string['revealreason'] = '显示学生姓名的理由';
$string['saveusage'] = '保存数据转储';
$string['selectoption'] = '选择选项';
$string['showusage'] = '显示数据转储';
$string['spapercheck'] = '与已存储的学生论文做比较';
$string['spapercheck_help'] = '当为论文处理原创性报告时与 Turnitin 学生论文存储库比较。如果这没有被选择的话，相似处指数百分比可能下降。';
$string['standardrepository'] = '标准存储库';
$string['student'] = '学生';
$string['studentreports'] = '显示原创性报告给学生';
$string['studentreports_help'] = '允许您向学生用户显示 Turnitin 原创性报告。如果设置为“确定”，则 Turnitin 生成的原创性报告将可供学生查看。';
$string['studentstatus'] = '已提交 {$a->modified}（论文代号：{$a->objectid}）';
$string['submissiondeleteerror'] = '无法删除提交件';
$string['submissionextract'] = '提交摘要';
$string['submissionfileerror'] = '您必须附加文件才能提交';
$string['submissionfiletypeerror'] = '不允许此文件类型。允许的类型为 ({$a})';
$string['submissiongeterror'] = '无法获得提交数据';
$string['submissiongrade'] = '成绩';
$string['submissionorig'] = '相似度';
$string['submissionpart'] = '提交部分';
$string['submissionpart_help'] = '选择此提交内容提交至的 Turnitin 作业部分';
$string['submissions'] = '提交内容';
$string['submissiontexterror'] = '您必须为此提交内容添加文本';
$string['submissiontitle'] = '提交标题';
$string['submissiontitle_help'] = '请为您提交的作品输入标题';
$string['submissiontitleerror'] = '您必须为此提交内容添加标题';
$string['submissiontype'] = '提交内容类型';
$string['submissiontype_help'] = '<p>显示您可以提交至 Turnitin 的提交内容类型。</p>';
$string['submissionupdateerror'] = '无法更新提交数据';
$string['submissionuploadsuccess'] = '您的提交内容已成功上传至 Turnitin。';
$string['submitpaper'] = '提交论文';
$string['submitpapersto'] = '存储学生论文';
$string['submitpapersto_help'] = '此设置使导师能够选择是否将论文存储在 Turnitin 学生论文存储库内。将论文提交至学生论文存储库的好处在于，提交至作业的学生论文将跟您现有或过去班级内的其他学生的提交内容做对比。如果您选择“无存储库”，您的学生的论文将不会被存储在 Turnitin 学生论文存储库内。';
$string['submitted'] = '已提交';
$string['submittoturnitin'] = '提交至 Turnitin';
$string['textsubmission'] = '文字提交';
$string['texttosubmit'] = '欲提交的文字';
$string['texttosubmit_help'] = '在此框中复制并粘贴您提交内容的文本';
$string['title'] = '标题';
$string['turnitinaccountid'] = 'Turnitin 帐户代号';
$string['turnitinaccountid_desc'] = '<b>[要求]</b><br />输入您的 Turnitin 主要帐户的代码';
$string['turnitinanon'] = '匿名标记';
$string['turnitinapiurl'] = 'Turnitin API URL';
$string['turnitindeleteconfirm'] = '删除论文将会使它们从提交列表和收件箱中移除，但不会彻底从\n Turnitin 数据库移除。\n\n是否确定要刪除此提交内容？此操作将无法撤消。';
$string['turnitindeletionerror'] = 'Turnitin 提交内容刪除失败。计算机上的 Moodle 副本已移除，但 Turnitin 內的提交内容无法刪除。';
$string['turnitinenrolstudents'] = '为所有学生注册';
$string['turnitinloading'] = '一致化数据';
$string['turnitinpart'] = '部分 {$a}';
$string['turnitinrefreshsubmissions'] = '更新提交内容';
$string['turnitinsecretkey'] = 'Turnitin 共享密钥';
$string['turnitinsecretkey_desc'] = '<b>[必需]</b><br />输入您的 Turnitin 共享密钥<br /><i>（由您的 Turnitin 管理员设置）</i>';
$string['turnitintooltwo'] = 'Turnitin 工具';
$string['turnitintooltwo:admin'] = '管理 Turnitin 工具';
$string['turnitintooltwo:grade'] = '评估 Turnitin 工具作业';
$string['turnitintooltwo:submit'] = '提交至 Turnitin 工具作业';
$string['turnitintooltwo:read'] = '查看 Turnitin 工具作业';
$string['turnitintooltwo:view'] = '查看 Turnitin 工具作业';
$string['turnitintooltwoadministration'] = 'Turnitin 作业 2 管理';
$string['turnitintooltwoagreement'] = '免责声明/协议';
$string['turnitintooltwoagreement_default'] = '我确认此提交内容是我的作品，并且接受所有可能因此提交而产生的侵权的责任。';
$string['turnitintooltwointro'] = '总结';
$string['turnitintooltwoname'] = 'Turnitin 作业名称';
$string['turnitintooltworesetdata0'] = '复制 Turnitin 作业<i>（创建副本，新建 Turnitin 课程）</i>';
$string['turnitintooltworesetdata1'] = '取代 Turnitin 作业 <i>（取代作业部分，重新使用 Turnitin 课程）</i>';
$string['turnitintooltworesetdata2'] = '让 Turnitin 作业保持原狀';
$string['turnitintooltworesetinfo'] = '为在此课程的 Turnitin 作业选择以下的一个选项';
$string['turnitintooltwoupdateerror'] = '无法更新 turnitintooltwo 数据';
$string['turnitintooltwoagreement_desc'] = '<b>[可选]</b><br />输入协议确认声明以供提交。<br />（<b>注意：</b>如果协议完全留空，则学生在提交时就无需确认协议）';
$string['turnitintooltwodeleteerror'] = '无法删除 turnitintooltwo 数据';
$string['turnitintooltwogeterror'] = '无法获得 turnitintooltwo 数据';
$string['turnitinuseanon'] = '使用匿名标记';
$string['turnitinuseanon_desc'] = '选择在为提交内容评分时是否允许匿名标记。<br /><i>（仅适用于已为其帐户配置了匿名标记的用户）</i>';
$string['turnitinusegrademark'] = '使用 GradeMark';
$string['turnitinusegrademark_help'] = '使用此设置可以选择是否使用 Turnitin GradeMark 或 Moodle 为提交内容评分';
$string['turnitinusegrademark_desc'] = '选择是否使用 GradeMark 或 Moodle 为提交内容评分。<br /><i>（仅适用于已为其帐户配置了 GradeMark 的用户）</i>';
$string['turnitinuserepository'] = '启用机构存储库';
$string['turnitinuserepository_desc'] = '选择是否在 Turnitin 作业內使用机构存储库。<br /><i>（仅适用于为其帐户启用了机构存储库的用户）</i>';
$string['turnitintutorsremove'] = '是否确定要在 Turnitin 中阻止此导师参加相应课程吗？';
$string['turnitinuserepository_help'] = '使用此设置在作业设置屏幕中启用机构存储库。<br /><i>（仅适用于已为其帐户启用了机构存储库的用户）</i>';
$string['tutorstatus'] = '{$a->submitted}/{$a->total}学生提交内容，{$a->graded} 提交内容 {$a->gplural} 已评分';
$string['type'] = '提交内容类型';
$string['types'] = '提交内容类型';
$string['types_help'] = '<p>提交内容可以有两种不同的形式。“复制和粘贴”或“文件上传”。</p>';
$string['unlinkusers'] = '停止链接用户';
$string['usercreationerror'] = 'Turnitin 用户创建失败';
$string['userdeleteerror'] = '无法删除用户数据';
$string['usergeterror'] = '无法获得用户数据';
$string['userstounlink'] = '结束链接的用户';
$string['userupdateerror'] = '无法更新用户数据';
$string['viewreport'] = '查看报告';
$string['wrongaccountid'] = '关联您的 Turnitin 课程时出错。您已配置的帐户为帐户 {$a->current}。此课程源自于帐户 {$a->backupid}。您只能还原源自同一个 Turnitin 帐户的课程。';
$string['copyassigndata'] = '复制 Turnitin 作业数据';
$string['replaceassigndata'] = '取代 Turnitin 作业数据';
$string['downloadexport'] = '外传';
$string['downloadorigzip'] = '压缩档（原始格式）';
$string['downloadpdfzip'] = '压缩档（PDF）';
$string['downloadgradexls'] = '分数外传（XLS）';
$string['turnitintutors'] = 'Turnitin 辅导教师';
$string['turnitintutorsadd'] = '添加 Turnitin 辅导教师';
$string['turnitintutorsallenrolled'] = '所有的辅导教师已注册在 Turnitin';
$string['turnitintutors_desc'] = '一下选择的辅导教师已注册至此 Turnitin 课程为辅导教师。已注册的辅导教师可以登录至 Turnitin 网站以进入此课程。';
$string['duplicatesfound'] = 'Moodle Direct 作业藉由 Turnitin API 连至同一个 Turnitin 作业时就会发生重复。这往往会造成问题，其中最常见的是，提交至其中一个收件箱的物件会出现在另一个收件箱。要解决这类问题，您应该删除重复作业或重新设置有重复作业出现的课程。<br /><br />我们找到以下的重复：';
$string['erater'] = '启用 e-rater 文法检查';
$string['eraternoun'] = 'E-rater';
$string['erater_handbook'] = 'ETS&copy; 手册';
$string['erater_dictionary'] = 'e-rater 字典';
$string['erater_categories'] = 'e-rater 类型';
$string['erater_spelling'] = '拼字';
$string['erater_grammar'] = '文法';
$string['erater_usage'] = '用法';
$string['erater_mechanics'] = '技巧';
$string['erater_style'] = '风格';
$string['erater_handbook_advanced'] = '进阶';
$string['erater_handbook_highschool'] = '高中';
$string['erater_handbook_middleschool'] = '中学';
$string['erater_handbook_elementary'] = '小学';
$string['erater_handbook_learners'] = '英文学习者';
$string['erater_dictionary_enus'] = '美式英文字典';
$string['erater_dictionary_engb'] = '英式英文字典';
$string['erater_dictionary_en'] = '美式和英式英语字典';
$string['turnitinuseerater'] = '启用 ETS&copy;';
$string['turnitinuseerater_desc'] = '选择是否启用 ETS&copy; 语法检查。<br /><i>（只有已在您的 Turnitin 帐户中启用了 ETS&copy; 批改系统的情况下才能启用此选项）</i>';
$string['student_read'] = '学生查看论文的时间：';
$string['student_notread'] = '学生尚未查看此论文。';
$string['relinkusers'] = '重新链接用户';
$string['unlinkrelinkusers'] = '解除链接/重新链接 Turnitin 用户';
$string['usersunlinkrelink'] = '解除链接/重新链接之用户';
$string['turnitinid'] = 'Turnitin 代码';
$string['turnitinsubmissionid'] = 'Turnitin 提交 ID';
$string['defaults'] = '默认设置';
$string['defaults_desc'] = '以下的设置将是用在新的 Moodle Direct Turnitin 作业 2 的实例上的默认设置';
$string['upgradeavailable'] = '有新的更新程式';
$string['coursemodidincorrect'] = '课程单元代码不正确';
$string['coursemisconfigured'] = '课程安装错误';
$string['coursemodincorrect'] = '课程单元不正确';
$string['no'] = '非';
$string['yes'] = '是';
$string['migrationassignmentcreated'] = '已创建迁移作业 - ID： {$a}';
$string['migrationassignmentpartcreated'] = '已创建迁移作业部分 - ID： {$a}';
$string['migrationassignmentcreationerror'] = '无法创建迁移作业 - 课程 {$a}';
$string['migrationassignmenterror1'] = '无法添加新课程单元至课程 {$a}';
$string['migrationassignmenterror2'] = '无法添加新课程单元至该组 - 课程 {$a}';
$string['migrationassignmenterror3'] = '无法为迁移的作业创建事件 - 课程 {$a}';
$string['migrationcoursecreateerror'] = '课程 {$a} 无法在 Moodle 上创建';
$string['migrationcoursecreatederror'] = '课程 {$a} 已重建，但在保存链接时出错';
$string['migrationcoursecreated'] = '在 Moodle 上重建 Turnitin 中的课程';
$string['migrationcoursegeterror'] = '无法取得 Turnitin 内的任何课程';
$string['migrationassignmentgeterror'] = '无法取得 Turnitin 内的任何作业';
$string['getassignmenterror'] = '无法从 Turnitin 获取迁移作业';
$string['checkupdateavailableerror'] = '无法为 Moodle Direct 查询版本更新';
$string['maxmarkserror'] = '最高分数必须在 0 和 100 之间';
$string['nosubmissiondataavailable'] = '没有任何其他提交内容数据';
$string['nointegration'] = '无整合';
$string['testingconnection'] = '测试连接至 Turnitin';
$string['integration'] = '整合';
$string['id'] = '代码';
$string['course'] = '课程';
$string['selectcoursecategory'] = '选择课程类型';
$string['module'] = '单元';
$string['source'] = '来源';
$string['similarity'] = '相似度';
$string['moodlelinked'] = '与 Moodle 链接';
$string['coursegettiierror'] = '无法从 Turnitin 获取课程数据';
$string['savecourseenddateerror'] = '在尝试在 Turnitin 内保存新的课程结束日期时出错';
$string['savecourseenddate'] = '保存新的课程结束日期';
$string['newcourseenddate'] = '新的课程结束日期';
$string['newenddatedesc'] = '为以下的课程选择新的结束日期，然後之後它会在 Turnitin 内更新。';
$string['close'] = '关闭';
$string['errors'] = '错误';
$string['setinstructordefaults'] = '设置这些值为作业默认值';
$string['setinstructordefaults_help'] = '这些设置将用于您创建的 Moodle Direct Turnitin 作业的所有新实例。它们将取代您的系统管理员指定的默认值并将供您专用。';
$string['messagesinbox'] = 'Turnitin 信息收件箱';
$string['downloadgrademarkzip'] = '下载所选的 GradeMark 文件';
$string['downloadorigfileszip'] = '下载原始文件';
$string['uploadingsubtoturnitin'] = '将您的提交内容上传至 Turnitin';
$string['emptycreatedfile'] = '您所尝试提交的文件为空白或已损坏';
$string['studentdataprivacy'] = '学生数据隐私设置';
$string['studentdataprivacy_desc'] = '可以配置以下设置以确保学生的个人数据不会通过 API 传送至 Turnitin。';
$string['enablepseudo'] = '启用学生隐私';
$string['enablepseudo_desc'] = '如果选择此选项，学生电子邮件地址将转换为 Turnitin API 调用的伪等效内容。<br /><i>（<b>注意：</b>如果有任何 Moodle 用户数据已与 Turnitin 同步，则无法更改此选项）</i>';
$string['pseudofirstname'] = '学生的假名';
$string['pseudofirstname_desc'] = '<b>[可选]</b><br />要显示在 Turnitin 文档查看器中的学生名字';
$string['pseudolastname'] = '学生的假姓';
$string['pseudolastname_desc'] = '学生的姓在Turnitin 文档查看器内显示';
$string['pseudolastnamegen'] = '自动生成姓氏';
$string['pseudolastnamegen_desc'] = '如果设为“是”并且假姓设为用户个人资料字段，则将自动用唯一标识符填充该字段。';
$string['pseudoemailsalt'] = '拟加密盐';
$string['pseudoemailsalt_desc'] = '<b>[可选]</b><br />可选的盐旨在增强生成的假学生电子邮件地址的复杂性。<br />（<b>注意：</b>盐应该保存不变，以确保一致的假电子邮件地址）';
$string['pseudoemaildomain'] = '假的电子邮件网域';
$string['pseudoemaildomain_desc'] = '<b>[选择性的]</b><br />假的电子邮件地址的可选域。（如果留空，则默认为 @tiimoodle.com）';
$string['pseudoemailaddress'] = '假电子邮件地址';
$string['instructor'] = '导师';
$string['files'] = '文件';
$string['filedeleteconfirm'] = '是否确定要删除此文件？此操作无法撤消。';
$string['filebrowser'] = 'Moodle Direct 文档查看器';
$string['deletable'] = '可删除';
$string['inactive'] = '未启用';
$string['created'] = '已创建';
$string['filename'] = '文件名';
$string['user'] = '用户';
$string['sprevious'] = '前';
$string['snext'] = '次';
$string['semptytable'] = '未找到任何结果。';
$string['slengthmenu'] = '显示 _MENU_ 条目';
$string['ssearch'] = '搜索：';
$string['sprocessing'] = '正在从 Turnitin 加载数据...';
$string['szerorecords'] = '无法显示任何记录。';
$string['sinfo'] = '正在显示第 _START_ 到 _END_ 个条目，共 _TOTAL_ 个条目。';
$string['unlinkedusers'] = '未连接用户';
$string['modulename_help'] = '创建一个 Turnitin Moodle Direct 作业，以便将 Moodle 中的活动关联到 Turnitin 上的作业。一旦建立关联，该活动便允许导师使用 Turnitin 文档查看器中提供的评估工具来评估学生的书面作业并提供相关反馈。';
$string['transmatch'] = '已翻译的相符功能';
$string['transmatch_desc'] = '确定已翻译的相符功能是否将作为作业设置屏幕上的设置来提供。<br /><i>（只有在您的 Turnitin 帐户中启用了已翻译的相符功能时，才会启用此选项）</i>';
$string['turnitintooltwo:addinstance'] = '添加 Turnitin 工具活动';
$string['copyrightagreementerror'] = '在提交前，请选中相应的框以表明您接受协议。';
$string['updatepart'] = '更新部分';
$string['grademark'] = 'GradeMark';
$string['launchgrademark'] = 'GradeMark';
$string['submissiondeleted'] = '提交内容已删除';
$string['tutoradded'] = '辅导教师已添加至 Turnitin 内的课程';
$string['tutoraddingerror'] = '添加辅导教师至 Turnitin 内的课程时出现问题';
$string['tutorremoved'] = '辅导教师已从 Turnitin 内的课程中移除';
$string['tutorremovingerror'] = '移除辅导教师至 Turnitin 内的课程时出现问题';
$string['noturnitinassignemnts'] = '尚无 Turnitin 作业';
$string['notutors'] = '尚无辅导教师加入 Turnitin 内的此课程';
$string['settings'] = '设置';
$string['faultcode'] = '错误代号';
$string['line'] = '列';
$string['message'] = '信息';
$string['code'] = '代号';
$string['userfinderror'] = '尝试在 Turnitin 中查找用户时出错';
$string['userjoinerror'] = '尝试在 Turnitin 中将用户加入课程时出错';
$string['userremoveerror'] = '尝试在 Turnitin 中将用户取消加入课程时出错';
$string['membercheckerror'] = '尝试检查已注册此课程的用户时出错';
$string['tiiusergeterror'] = '尝试从 Turnitin 中获取用户细节时出错';
$string['createassignmenterror'] = '尝试在 Turnitin 中创建作业时出错';
$string['editassignmenterror'] = '尝试在 Turnitin 中编辑作业时出错';
$string['deleteassignmenterror'] = '尝试在 Turnitin 中删除作业时出错';
$string['createsubmissionerror'] = '尝试在 Turnitin 中创建提交内容时出错';
$string['updatesubmissionerror'] = '尝试将提交内容重新提交至 Turnitin 时出错';
$string['tiisubmissiongeterror'] = '尝试从 Turnitin 中获取提交内容时出错';
$string['tiisubmissionsgeterror'] = '尝试从 Turnitin 中获取此作业的提交内容时出错';
$string['enrolling'] = '将学生注册到 Turnitin';
$string['tiiassignmentgeterror'] = '尝试从 Turnitin 中获取作业时出错';
$string['turnitinstudents'] = 'Turnitin 学生';
$string['turnitinstudentsremove'] = '是否确定要在 Turnitin 中阻止此学生参加相应课程吗？';
$string['studentremoved'] = '系统已在 Turnitin 中阻止学生参加相应课程';
$string['studentremovingerror'] = '在 Turnitin 中阻止学生参加相应课程时出现问题';
$string['turnitinstudents_desc'] = '以下被选择的用户已註册至此 Turnitin 课程。註册学生可藉由登录 Turnitin 网页授权进入此课程。';
$string['coursebrowserdesc'] = '您可以搜索 Turnitin 内的课程以在 Moodle 上重新创建以下课程';
$string['courseexistsmoodle'] = '此课程目前在 Moodle 内显示为：';
$string['coursetitle'] = '课程标题';
$string['coursetitleerror'] = '您必须提供课程标题以供搜索';
$string['createmoodlecourses'] = '重建课程';
$string['createmoodleassignments'] = '在 Moodle 中重新创建所选课程的所有作业吗？';
$string['recreatemulticlasses'] = '您所选择的课程现在正重建。根据您所选择的课程数，这有可能会花几分钟。';
$string['recreatemulticlassescomplete'] = '课程重新创建现已完成。已成功创建 {$a->completed} 个，共 {$a->total} 个。';
$string['createcourse'] = '新建 Moodle 课程';
$string['linkcourse'] = '链接课程至现存的 Moodle 课程';
$string['selectcourse'] = '选择 Moodle 课程';
$string['defaultcoursetiititle'] = 'Turnitin 内的课程';
$string['or'] = '或';
$string['downloadassignment'] = '下载作业至 Moodle';
$string['assignmenttitle'] = '新作业标题';
$string['defaultassignmenttiititle'] = 'Turnitin 内的作业';
$string['revealdesc'] = '请在下方留下显示学生姓名的原因。';
$string['noreason'] = '没有特定的原因';
$string['unanonymiseerror'] = '当尝试显示学生姓名时出错';
$string['digitalreceipt'] = '数字回执';
$string['viewdigitalreceipt'] = '查看数字回执';
$string['noscript'] = 'Turnitin 需要 Javascript 但是您的浏览器并未启用它。请在您的浏览器内启用 Javascript 以使您能够利用 Turnitin 的所有功能。';
$string['noscriptsummary'] = 'Turnitin 需要 Javascript 但是您的浏览器并未启用它。若未启用的话，您将无法进入 Turnitin。';
$string['noscriptula'] = '（由于您没有启用 javascript，因此在接受 Turnitin 用户协议后，您必须手动更新此页面才能提交）';
$string['showsummary'] = '显示作业摘要';
$string['hidesummary'] = '隐藏作业摘要';
$string['marksavailable'] = '标记可用';
$string['deletepart'] = '删除部分';
$string['partdeleted'] = '作业部分已被删除';
$string['turnitin'] = 'Turnitin';
$string['coursebrowserassignmentdesc'] = '在下列的表格，您可以选择最多 5 个作业以在 Moodle 中创建为复制 Turnitin 作业的部分。';
$string['turnitinoroptions'] = '原创性报告选项';
$string['turnitingmoptions'] = 'GradeMark 选项';
$string['attachrubric'] = '将评分表附加至此作业';
$string['norubric'] = '无评分表';
$string['otherrubric'] = '使用属于其他导师的评分表';
$string['attachrubricnote'] = '注意：学生将可以在提交前查看附加的评分表及其内容。';
$string['changerubricwarning'] = '更改或分离评分表将从此作业的论文中移除所有现有的评分表分数，包括之前已标记的评分卡。之前已评分的论文的总成绩将会被保留。';
$string['launchrubricmanager'] = '启动评分表管理工具';
$string['launchrubricview'] = '查看用于标记的评分表';
$string['launchrubricviewshort'] = '标记评分表';
$string['launchquickmarkmanager'] = '启动 Quickmark 管理工具';
$string['launchpeermarkmanager'] = '启动 Peermark 管理工具';
$string['launchpeermarkreviews'] = '启动 Peermark 评价';
$string['peermark'] = 'PeerMark';
$string['peermarkassignments'] = 'PeerMark 作业';
$string['showpeermark'] = '显示 PeerMark 作业';
$string['hidepeermark'] = '隐藏 PeerMark 作业';
$string['noofreviewsrequired'] = '必要评鑑总数';
$string['showpeermarkinstructions'] = '显示 PeerMark 指示';
$string['hidepeermarkinstructions'] = '隐藏 PeerMark 指示';
$string['turnitinenablepeermark'] = '启用 PeerMark 作业';
$string['turnitinenablepeermark_desc'] = '选择是否允许创建 Peermark 作业。<br/><i>（仅适用于已为其帐户配置了 Peermark 的用户）</i>';
$string['nonenrolledstudent'] = '没有注册的学生';
$string['startdatenotyearago'] = '开始日期不能过於 1年前';
$string['searchcourses'] = '搜索课程';
$string['errorsdesc'] = '尝试将以下文件上传至 Turnitin 时出现问题。';
$string['tiiexplain'] = 'Turnitin 为商务产品。您必须付订购费才能使用此服务。有关更多信息，请访问 <a href=http://docs.moodle.org/en/Turnitin_administration>http://docs.moodle.org/en/Turnitin_administration</a>';
$string['useturnitin'] = '启用 Turnitin';
$string['turnitinconfig'] = 'Turnitin 剽窃 Plugin 配置';
$string['studentdisclosuredefault'] = '所有上传的文件都将提交至剽窃侦查服务 Turnitin.com';
$string['studentdisclosure'] = '学生透露';
$string['studentdisclosure_help'] = '此文本将在文件上传页面上向所有学生显示。';
$string['settingsinserterror'] = '尝试将设置插入数据库中时出错';
$string['settingsupdateerror'] = '尝试更新数据库设置时出错';
$string['config'] = '配置';
$string['configupdated'] = '配置已更新';
$string['turnitindefaults'] = 'Turnitin 剽窃 Plugin 默认设置';
$string['turnitinpluginsettings'] = 'Turnitin 剽窃 Plugin 设置';
$string['defaultsdesc'] = '以下设置为在活动单元内启用 Turnitin 时设置的默认值';
$string['compareinstitution'] = '将已提交的文件与在此机构内提交的论文进行比较';
$string['defaultinserterror'] = '尝试将默认设置值插入数据库时出错';
$string['defaultupdateerror'] = '尝试更新数据库中的默认设置值时出错';
$string['defaultupdated'] = 'Turnitin 默认值已更新';
$string['pp_createsubmissionerror'] = '剽窃 plugin 在创建提交内容时出错';
$string['pp_updatesubmissionerror'] = '剽窃 plugin 在更新提交内容时出错';
$string['coursestomigrate'] = '您有 {$a} 门课程可从 Turnitin 复原';
$string['gradingtemplate'] = '评分模板';
$string['allownonor'] = '允许提交任何文件类型吗？';
$string['allownonor_help'] = '此设置将允许提交任何文件类型。如果此选项设为“是”，则在可行的前提下，系统会检查提交内容的原创性，提交内容将可供下载并且 GradeMark 反馈工具将可供使用。';
$string['submitnothing'] = '为尚未提交的学生启用评分功能';
$string['submitnothingwarning'] = '为尚未提交文件的学生单击灰笔将创建一个评分模板，您可以使用该模板为学生作业提供 GradeMark 反馈。评分模板会取代提交内容并将阻止学生提交至不允许重新提交的作业。<br><br>是否确定要评分而不提交？';
$string['draftsubmit'] = '文件应在何时提交至 Turnitin？';
$string['submitondraft'] = '在首次上传时提交文件';
$string['submitonfinal'] = '当学生发送以供标记时提交文件';
$string['turnitindiagnostic'] = '启用诊断模式';
$string['turnitindiagnostic_desc'] = '<b>[警告]</b><br />启用诊断模式来追踪 Turnitin API 的问题。';
$string['autorefreshgrades'] = '自动更新成绩/分数';
$string['autorefreshgrades_help'] = '默认情况下，在每次调用 Turnitin 后，Moodle 集成插件将试图在 Turnitin 中自动拉取所做更改。您可以使用此选项关闭此功能，但是为了保持两个系统之间的同步，您将需要从 Moodle 中频繁手动更新成绩和原创性分数。';
$string['yesgrades'] = '确定，自动更新原创性分数与成绩';
$string['nogrades'] = '不，我将自行更新原创性分数和成绩';
$string['submissionagreementerror'] = '您必须接受此协议才能提交';
$string['noxmlwriterlibrary'] = '欲使用此插入程式，您将需要在您的伺服器上安装 PHP XMLWriter 延伸软件。';
$string['checkupgrade'] = '检查可用的升级';
$string['checkingupgrade'] = '检查可用的升级中';
$string['usinglatest'] = '您在使用最新的版本！';
$string['useturnitin_mod'] = '启用 Turnitin {$a}';
$string['notorcapable'] = '无法为此文件生成原创性报告。';
$string['redirecttoeula'] = '我们正在将您重定向到终端用户许可证协议';
$string['filedoesnotexist'] = '文件已被删除';
$string['eventremoved'] = '此活动已从 cron 队列中移除，也不会再被处理。';
$string['partnametoolarge'] = '部分名称太大。请限制至 40 个字符。';
$string['enableperformancelogs'] = '启用网路性能日志记录';
$string['enableperformancelogs_desc'] = '若启用，每个给 Turnitin 伺服器的请求将会被记录在 {tempdir}/turnitintooltwo/logs';
$string['listsubmissions'] = '列举提交内容';
$string['viewsubmission'] = '查看提交内容';
$string['listsubmissionsdesc'] = '用户已查看课程提交内容列表';
$string['viewsubmissiondesc'] = '用户已查看提交内容';
$string['addsubmissiondesc'] = '用户已添加提交内容';
$string['deletesubmissiondesc'] = '用户已删除提交内容';
$string['turnitinrepositoryoptions'] = '论文存储库作业';
$string['turnitinrepositoryoptions_desc'] = '为 Turnitin 作业选择存储库选项。<br /><i>（机构存储库仅用于为其帐户启用了此选项的用户）</i>';
$string['turnitinrepositoryoptions_help'] = '用此设置以更改作业设置屏幕内可用的存储库选项。<br /><i>（机构存储库仅用于为其帐户启用了此选项的用户）</i>';
$string['repositoryoptions_0'] = '启用导师标准存储库选项';
$string['repositoryoptions_1'] = '启用导师扩展存储库选项';
$string['repositoryoptions_2'] = '将所有论文提交至标准存储库';
$string['repositoryoptions_3'] = '请勿将任何论文提交至存储库';
$string['turnitinula_btn'] = '请单击此处阅读并接受协议。';
$string['turnitinula'] = '在提交之前，您必须接受最新的 Turnitin 用户协议。';
$string['upgradenotavailable'] = '无可用的更新';
$string['turnitintoolofflineerror'] = '我们遇到临时问题。请稍后再试。';
$string['offlinestatus'] = 'Turnitin 已设为离线。（变量 $CFG->tiioffline 已设为 true。）';
$string['disableanonconfirm'] = '这样做将会永久停用此作业上的匿名标记。您确定吗？';
$string['uniquepartname'] = '部分名称必须唯一';
$string['closebutton'] = '关闭';
$string['reportgenspeed_help'] = '此作业设置有三个选项：“立即生成报告(不允许重新提交)”、“立即生成报告(在截止日期之前允许重新提交)”和“在截止日期生成报告(在截止日期之前允许重新提交)”<br /><br />“立即生成报告(不允许重新提交)”选项可在学生进行提交时立即生成原创性报告。选择此选项后，您的学生将无法重新提交作业。<br /><br />要允许重新提交，请选择“立即生成报告(在截止日期之前允许重新提交)”选项。这允许学生在截止日期之前继续向作业重新提交报告。处理重新提交的原创性报告可能最长需要 24 小时。<br /><br />“在截止日期生成报告(在截止日期之前允许重新提交)”选项只会在作业的截止日期生成原创性报告。此设置将允许系统在创建原创性报告后对提交至作业的所有论文进行相互比较。';
$string['submissiondate'] = '提交日期';
$string['receiptassignmenttitle'] = '作业标题';
$string['refid'] = '参照代号';
$string['turnitinpaperid'] = 'Turnitin 论文代号';
$string['submissionauthor'] = '提交作者';
$string['receiptparagraph'] = '此回执会确认 Turnitin 已收到您的论文。您可以在下方找到有关您提交内容的回执信息。';
$string['objectid'] = 'Turnitin 论文代号';
$string['anonalert'] = '您的公佈日期在当前时间之前。如果您保存的话，将会永久停用此作业上的匿名标记。';
$string['turnitinapiurl_desc'] = '<b>[必需]</b><br />选择 Turnitin API URL';
$string['tii_submission_failure'] = '请谘询您的导师或 Moodle 管理员以了解更多详情';
$string['turnitinrefreshingsubmissions'] = '更新提交内容';
$string['turnitinanon_help'] = '您可以通过将此值设为“是”来配置您的 Turnitin 作业。一旦提交，便无法再停用匿名标记。';
$string['digital_receipt_subject'] = '这是您的 Turnitin 数字回执';
$string['digital_receipt_message'] = '尊敬的 {$a->firstname} {$a->lastname}，<br /><br />您已于 <strong>{$a->submission_date}</strong>将文件 <strong>{$a->submission_title}</strong> 成功提交至 <strong>{$a->course_fullname}</strong> 课堂的分配 <strong>{$a->assignment_name}{$a->assignment_part}</strong>。您的提交 ID 为 <strong>{$a->submission_id}</strong>。可以通过文档查看器中的作业收件箱或“打印/下载”按钮查看并打印您的完整数字回执。<br /><br />感谢您使用 Turnitin，<br /><br />Turnitin 团队';
$string['messageprovider:submission'] = 'Turnitin 作业数字回执通知';
$string['errorenrollingall'] = '在 Turnitin 上注册全部学生时出错 - 请咨询您的系统管理员';
$string['ppassignmentcreateerror'] = '此单元无法在 Turnitin 上创建，请查阅您的 API 日志以获得更多信息';
$string['pp_classcreationerror'] = '此课程无法在 Turnitin 上创建，请查阅您的 API 日志以获得更多信息';
$string['pp_submission_error'] = 'Turnitin 为您的提交返回了一个错误：';
$string['turnitinppulapre'] = '要向 Turnitin 提交文件，您必须首先接受我们的 EULA。选择不接受我们的 EULA 只会将您的文件提交到 Moodle。单击此处以接受。';
$string['turnitinppulapost'] = '您的文件尚未提交至 Turnitin。请单击此处接受我们的 EULA。';
$string['listsubmissionsdesc_student'] = '用户在课程中查看了其提交收件箱';
$string['gradenosubmission'] = '用户为尚未提交的具有代码的用户启用评分功能';
$string['turnitinstatus'] = 'Turnitin 状态';
$string['resubmittoturnitin'] = '重新提交至 Turnitin';
$string['resubmitting'] = '重新提交';
$string['addresubmissiontiidesc'] = '用户重新提交发送至 Turnitin';
$string['addsubmissiontiidesc'] = '用户提交发送至 Turnitin';
$string['deletesubmissiontiidesc'] = '用户已从 Turnitin 删除提交内容';
$string['download'] = '下载';
$string['grademarkzip'] = '所选的 GradeMark 文件';
$string['origfileszip'] = '原始文件';
$string['sharedrubric'] = '已共享评分表';
$string['resubmitselected'] = '重新提交所选文件';
$string['turnitininboxlayout'] = '作业页面布局';
$string['turnitininboxlayout_desc'] = '选择“Turnitin 作业”页面应该显示导航还是全宽显示。';
$string['layoutoptions_0'] = '全宽';
$string['layoutoptions_1'] = 'Moodle 默认 - 带导航';
$string['messagenonsubmitters'] = '通知非提交者';
$string['nonsubmittersformdesc'] = '请输入下面的消息以发送至尚未提交至此作业的学生。';
$string['nonsubmitterssubject'] = '课题';
$string['nonsubmittersmessage'] = '信息';
$string['nonsubmitterssendtoself'] = '将此消息的副本发送给我';
$string['nonsubmitterssubmit'] = '发送电子邮件';
$string['nonsubmitterserror'] = '请提供电子邮件的主题和消息';
$string['nonsubmitterssubjecterror'] = '请提供电子邮件的主题';
$string['nonsubmittersmessageerror'] = '请提供电子邮件的消息';
$string['nonsubmittersformsuccess'] = '您的消息已发送至非提交者。';
$string['messageprovider:nonsubmitters'] = 'Turnitin 作业非提交者通知';
$string['checkagainstnote'] = '注意：如果您没有为下面至少一个“做比较...”选项选择“是”，则不会生成“原创性”报告。';
$string['anonblindmarkingnote'] = '注意：已删除单独的 Turnitin 匿名标记设置。Turnitin 将使用 Moodle 隐蔽标记设置确定匿名标记设置。';
$string['displaygradesas'] = '成绩显示';
$string['displaygradesas_help'] = '此选项设置成绩的显示模式，选项显示为百分比或显示为分数，例如 45/60';
$string['displaygradesasfraction'] = '将成绩显示为分数（例如 89/100）';
$string['displaygradesaspercent'] = '将成绩显示为百分比（例如 89&#37;）';
$string['genspeednote'] = '注意：生成原创性报告以供重新提交可能会有 24 小时的延迟。';
$string['cronsubmittedsuccessfully'] = '提交：课程 {$a->coursename} 中分配 {$a->assignmentname} 的 {$a->title}（TII ID：{$a->submissionid}）已成功提交至 Turnitin。';
$string['ppassignmentediterror'] = '单元 {$a->title}（TII ID：{$a->assignmentid}）无法在 Turnitin 上编辑，请查看您的 API 日志了解更多信息';
$string['nombstringlibrary'] = '要使用此插件，您将需要在您的服务器上安装 PHP mbstring 扩展。';
$string['receipt_instructor_copy'] = '已完成对课程 <strong>{$a->course_fullname}</strong> 中的作业 <strong>{$a->assignment_name}{$a->assignment_part}</strong> 的标题为 <strong>{$a->submission_title}</strong> 的提交。<br /><br />提交 ID：<strong>{$a->submission_id}</strong><br />提交日期： <strong>{$a->submission_date}</strong><br />';
$string['receipt_instructor_copy_subject'] = '对作业进行了提交';
$string['instructorreceipt'] = '向导师通知提交';
$string['instructorreceipt_desc'] = '选择在提交作业时是否向课程的每位导师发送通知。';
$string['loadingdv'] = '正在加载 Turnitin 文档查看器...';
$string['messageprovider:notify_instructor_of_submission'] = 'Turnitin 作业导师数字回执通知';
$string['postdate_warning'] = '请注意，更改作业日期可能会影响成绩何时对学生可见以及学生的身份何时向导师显示。';
$string['task_name'] = 'Turnitintooltwo Cron 任务';
$string['crontaskmodeactive'] = 'Turnitintooltwo - 由于活动任务模式而中止了 Cron 调用';
$string['restorationheader'] = 'Turnitin 课程复原';
$string['turnitinhelpdesk'] = 'Turnitin Helpdesk';
$string['helpdesklink'] = '需要关于 Turnitin 的帮助？';
$string['turnitinsettingshelpwizard'] = '为导师启用 Turnitin Helpdesk';
$string['turnitinsettingshelpwizard_desc'] = '选择导师是否能够从 Moodle 中访问 Turnitin Helpdesk 向导。';
$string['tiiaccountconfig'] = 'Turnitin 帐户配置';
$string['tiiaccountsettings'] = 'Turnitin 帐户设置';
$string['tiiaccountsettings_desc'] = '请确保这些设置与您的 Turnitin 帐户中配置的相符，否则您可能会在作业创建和/或学生提交时遇到问题。';
$string['tiimiscsettings'] = '其他插件设置';
$string['tiidebugginglogs'] = '调试和记录';
$string['diagnosticoptions_0'] = '关闭';
$string['diagnosticoptions_1'] = '标准';
$string['diagnosticoptions_2'] = '调试';