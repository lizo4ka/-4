<?php

namespace app\modules\glr\models;

use yii\helpers\Html;


$this->title = 'Форма ГЛР 1.4';
$this->params['breadcrumbs'][] = $this->title;
?>


<style>
	<!--
	.statementGlr14 {
		font-family: "Times New Roman", serif;
		font-size: 10pt
	}

	.docHeader {margin: 0 0 20pt 20cm; width: 7sm}
	.docHeader .oivName {padding-top: 12pt}
	.docHeader .formnum {font-size: 10pt; text-align: right}

	.docFooter {margin: 20pt 20pt 0 309pt}
	.docFooter p {text-transform:uppercase}

	p {margin: 0; font-size: 12pt}
	.sub {font-size: 10pt; text-align: center; padding: 1pt 0 0 0; border-top: solid}
	.sub.address_subject {margin-left: 277pt;}
	.sub.address_forestry {margin-left: 161pt;}
	.sub.address_district{margin-left: 140pt}
    .sub.address_municip{margin-left: 168pt}
    .sub.address_category {margin-left: 415pt;}
	.statementGlr14 td {vertical-align: top;}
	.addressPart {margin: 6pt 0 12pt}

	p.anketa {margin:12pt 0 6pt 0;}
	table.statement {border-collapse: collapse; border: solid 1pt; font-size: 9pt}
	table.statement td {text-align:center}
	table.statement .colnums td {font-size: 7pt; color: #999}
	table.statement td {border: solid 1pt; padding: 0 1.4pt 0 1.4pt}
	.note_beforetable {font-size: 9pt; text-align: right;}
	.note_aftertable {font-size: 9pt}

	.signature p:nth-child(1) {margin-top:6pt}
	.signature p:nth-child(2) {margin-top: 12pt; padding-left: 30pt}
	.signature span:nth-child(1) {}
	.signature span:nth-child(2) {padding-left: 130pt}
	.signature span:nth-child(3) {padding-left: 130pt}
	.signature .sub {margin: 0 260pt 0 30pt}

	-->
</style>


<?php

// Данные заявителя из профиля залогиненого польлзователя
$user_details = \app\models\UserDetails::find()->where(['id' => \Yii::$app->user->identity->id])->one();

// Объект с пересеченными выделами
if (isset($stripsObj)) $intersectedStrips = json_decode($stripsObj);
else $intersectedStrips = '';


?>

<br>
<?php 


function formatter($num) {
	return number_format( $num / 10000, 2, ',', ' ');
}

$count = 0;

$nameozu = array(11=>
	"Берегозащитные участки лесов, расположенные вдоль водных объектов",
	"Почвозащитные участки лесов, расположенные вдоль склонов оврагов",
	"Опушки лесов, граничащие с безлесными пространствами",
	"Плюсовые насаждения",
	"Лесосеменные плантации",
	"Постоянные лесосеменные участки",
	"Маточные плантации",
	"Архивы клонов плюсовых деревьев",
	"Испытательные культуры",
	"Популяционно-экологические культуры",
	"Географические культуры",
	"Участки леса с наличием плюсовых деревьев",
	"Заповедные лесные участки",
	"Участки лесов с наличием реликтовых и эндемичных растений",
	"Места обитания редких и находящихся под угрозой исчезновения диких животных",
	"Полосы леса в горах вдоль верхней его границы с безлесным пространством",
	"Небольшие участки лесов, расположенные среди безлесных пространств",
	"Защитные полосы лесов вдоль гребней и линий водоразделов",
	"Участки леса на крутых горных склонах",
	"Особо охранные части государственных природных заказников и других особо охраняемых природных территорий",
	"Леса в охранных зонах государственных природных заповедников, национальных парков и иных особо охраняемых природных территорий, а также территории, зарезервированные для создания особо охраняемых природных территорий федерального значения",
	"Объекты природного наследия",
	"Участки лесов вокруг глухариных токов",
	"Участки лесов вокруг естественных солонцов",
	"Полосы лесов по берегам рек или иных водных объектов, заселенных бобрами",
	"Медоносные участки лесов",
	"Постоянные пробные площади",
	"Участки лесов вокруг санаториев, детских лагерей, домов отдыха, пансионатов, туристических баз и других лечебных и оздоровительных учреждений",
	"Участки лесов вокруг минеральных источников, используемых в лечебных и оздоровительных целях или имеющих перспективное значение",
	"Полосы лесов вдоль постоянных, утвержденных в установленном порядке трасс туристических маршрутов федерального или регионального значения",
	"Участки лесов вокруг сельских населенных пунктов и садовых товариществ"
);


		// Разбор адресной части и перечисление выделов, если есть данные по пересечке

		if($intersectedStrips) {

			$areaskp  = array_fill(11, 31, 0); //сумма площадей по конктрентому ОЗУ

			
			foreach ($intersectedStrips as $arritem) {

				// **** Контроль входных данных для обработки запросов ***
				// Если все необходимые данные присутствуют - разбираем массив на адресность
				if (isset($arritem->sri) &&
					isset($arritem->mu) &&
					isset($arritem->gir) &&
					isset($arritem->kv) &&
					isset($arritem->sknr) ) 
				{

					// Выводим адресную часть всех выделов, попавших под пересечку
					// сгруппированную по субъектам, лесничествам, уч. л-вам и кварталам
					
					//*** СУБЪЕКТЫ РФ ***
					if (!isset($sri) || $sri != $arritem->sri) {
						
						$sri = $arritem->sri;
						$fedSub = FederalSubject::find()->where(['federal_subject_id' => $sri])->one();
						
						// Наименование ОИВ
						$oiv = (OivSubject::find()->where(['fed_subject' => $sri])->one() )->name;
						$numoiv = (OivSubject::find()->where(['fed_subject' => $sri])->one() )->id;
						$telefone = (OivSubject::find()->where(['fed_subject' => $sri])->one() )->phone;



						//Руководитель ОИВ
						$person = (OivSubjectPerson::find()->where(['oiv_subject' => $numoiv, 'priority' => 1])->one())->fio;
					}

					//*** ЛЕСНИЧЕСТВА ***
					if (!isset($mu) || $mu != $arritem->mu) {

						$mu = $arritem->mu;
						$frstry = Forestry::find()->where(['KOD_SUB' => $sri, 'KOD_LN' => $mu])->one();
						
					?>

<!-- Для каждого лесничества рисуем отдельную выписку -->
<div class="statementGlr14">

	<div class="docHeader">
		<p class="formnum">Форма 1.4.</p>
		<p class="oivName"><?= $oiv ?></p>
		<p class="sub">(наименование органа государственной власти)</p>
	</div>

	<p style='text-align:center; margin-bottom:12pt;'>
		<b>ВЫПИСКА ИЗ ГОСУДАРСТВЕННОГО ЛЕСНОГО РЕЕСТРА № <?= $orderId ?> 
		<br>Сведения об особо защитных участках лесов (ОЗУ) <br> на <?php $w = date('w'); //порядковый номер недели
			if ($w < 6)
				//считаем разницу дней: общее кол-во дней в неделю (7) - пятница (5) + текущий день недели 
				$def = 2 + $w; 
			else
				$def = 1; //если текущий день сб, то разница в днях равна 1	
			echo  date('d.m.Y', strtotime("-$def day")); //выводим дату прошлой пт ?> г.</b>
	</p>

	<p>
		<br>Наименование субъекта Российской Федерации &nbsp;&nbsp;&nbsp; 
		<?= (FederalSubject::find()->where(['federal_subject_id' => $sri ])->one())->name ?>
	</p>
	
	<p class="sub address_subject">&nbsp;</p>

	<p>Наименование категории земель, на которой расположено лесничество</p> 

    <p class="sub address_category">&nbsp;</p>

    <p>Муниципальное образование</p>

    <p class="sub address_municip">&nbsp;</p>

	<p>Наименование лесничества &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= (Forestry::find()->where(['KOD_SUB' =>$sri, 'KOD_LN' => $mu])->one())->LN_NAME ?></p>

	<p class="sub address_forestry">&nbsp;</p>

	<p>Участковое лесничество &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= (SubforestryFf::find()->where(['subject_kod' => $sri, 'forestry_kod' => $mu, 'subforestry_kod' => $arritem->gir])->one()->subforestry_name) ?> 
     </p>

    <p class="sub address_district">&nbsp;</p>

	<p class="note_beforetable"></p>

	<table class="statement" cellspacing=0 cellpadding=0>
		<thead>
			<tr>
				<td rowspan="2">Виды лесов по целевому назначению и категории защитных лесов</td>
				<td rowspan="2">Наименование ОЗУ</td>
				<td colspan="4">Местоположение лесного участка</td>
				<td rowspan="2">Площадь, га</td>
			</tr>
			<tr>
				<td>наименование участкового лесничества</td>
				<td>наименование урочища*</td>
				<td>номер лесного квартала</td>
				<td>номер лесотакционного выдела</td>
			</tr>
		</thead>

		<tr class="colnums">
			<td>1</td><td>2</td><td>3</td><td>4</td><td>5</td>
			<td>6</td><td>7</td>
		</tr>

		<?php

					}


					//*** УЧАСТКОВЫЕ ЛЕСНИЧЕСТВА ***
					if (!isset($gir) || $gir != $arritem->gir) {

					// Если изменился номер участкового лесничества (существует прежний номер) - печатаем строку с данными
						if(isset($gir)) {
							echo "
							<tr>
								<td></td>
								<td></td>
								<td>".$subfrstry->ULN_NAME."</td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							";
							$start_count = 0;
							// printRow();
												
						}
						
						$start_count = 1; // начали рассчеты по участковому лесничеству: флаг


						$gir = $arritem->gir;
						$subfrstry = Subforestry::find()->where(['KOD_SUB' => $sri, 'KOD_LN' => $mu, 'KOD_ULN' => $gir])->one();
						
					}


					if($arritem->skp != 0){
					// Вычисляем нужный ОЗУ и категорию защитности

						if ($arritem->mk != 0) {
							if ($arritem->mk < 200200) {
								for ($i=11; $i <= 41 ; $i++) { 
									if($arritem->skp == $i){
										$skpname = $nameozu[$i];
										$areaskp[$i] += $arritem->pls; //// подсчет суммы площадей по конктрентому ОЗУ
									}
								}
								$category = 'Защитные';
							}
							elseif ($arritem->mk == 200200 || $arritem->mk == 300300 || $arritem->mk == 300400) {
								for ($i=11; $i <= 41 ; $i++) { 
									if($arritem->skp == $i){
										$skpname = $nameozu[$i];
										$areaskp[$i] += $arritem->pls;
									}
								}
								$category = 'Эксплуатационные';
							}
							elseif ($arritem->mk == 400500) {
								for ($i=11; $i <= 41 ; $i++) { 
									if($arritem->skp == $i){
										$skpname = $nameozu[$i];
										$areaskp[$i] += $arritem->pls;
									}
								}
								$category = 'Резервныее';
							}
						}	
						$numbskp[$count] = $arritem->skp; //массив с номерами ОЗУ
						//формируем массив с данными для таблицы (категория защитности, название озу, квартал, выдел, площадь)
						$ozu[$count] = ["categ" => $category, "name" => $skpname, "quarter" => $arritem->kv, "strip" => $arritem->sknr, "area" => $arritem->pls]; 
						$count++;
					}
				} 
			}


			if (isset($numbskp)) {

				asort($numbskp); //сортировка массива с номерами озу по возрастанию

			
				array_multisort($ozu, $numbskp); //сортировка массива с данными относительно массива $numbskp

						if($start_count) {
							
							$start_count = 0;

							for ($i=0; $i < count($numbskp); $i++) { 
								echo "
								<tr>
									<td>".$ozu[$i]["categ"]."</td>
									<td>".$ozu[$i]["name"]."</td>
									<td>".$subfrstry->ULN_NAME."</td>
									<td></td>
									<td>".$ozu[$i]["quarter"]."</td>
									<td>".$ozu[$i]["strip"]."</td>
									<td>".$ozu[$i]["area"]."</td>
								</tr>
							";
							}

							
							$allskp = 0; //сумма всех площадей
							for ($i=11; $i <= 41 ; $i++) { 
								if ($areaskp[$i] != 0){
									echo "
										<tr>
											<td>Итого</td>
											<td>".$nameozu[$i]."</td>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td>".$areaskp[$i]."</td>
										</tr>
										";
									$allskp += $areaskp[$i]; //подсчет суммы всех-всех площадей
								}
							}

							echo "
										<tr>
											<td>Итого по всем ОЗУ</td>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td>".$allskp."</td>
										</tr>
										";									
							
						}
			}
			

?>
	</table>


	<p class="anketa">
		Сведения о заинтересованном лице:<br>
		Ф.И.О. физического лица/полное наименование юридического лица: <strong><?= $user_details->fio ?></strong><br>
		Адрес: <strong><?= $user_details->address ?></strong><br><br>
	</p>

	

	<p style='margin-top:12pt;'>Руководитель:
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<?= $person ?></p> 
	<p class="sub" style='margin: 0 30pt 0 82pt; text-align: left'>
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; (подпись)
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; (Ф.И.О.)</p>

	<p>Должностное лицо, ответственное за составление формы</p>
	<p class="sub" style='margin: 0 30pt 0 330pt;'>(должность)
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; (подпись)
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Ф.И.О.</p>

	<p>&nbsp;</p> <?= $telefone ?>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<?php echo  date('d.m.Y') ; ?>
	<p class="sub" style='text-align: left; margin: 0 30pt 0 0;'>(номер контактного телефона с указанием кода)
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; (дата составления документа)</p>





</div>

