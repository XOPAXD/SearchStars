<?php 
	//conexao com o banco
	$servidor = 'mysql.hostinger.com.br';
	$usuario = 'u892128214_st';
	$senha = 'xopa1234';
	$banco = 'u892128214_st';

	
	// Conecta-se ao banco de dados MySQL
	$mysqli = new mysqli($servidor, $usuario, $senha, $banco);
	// Caso algo tenha dado errado, exibe uma mensagem de erro
	if (mysqli_connect_errno()) trigger_error(mysqli_connect_error());

	header('Content-Type: text/html; charset=UTF-8',true); 
	if($_FILES)
	{
		
		//FAZ O UPLOAD DO ARQUIVO
		$uploads_dir = 'uploads_csv/';

		$extok = array(  'csv','txt' );

		$img = array();

		if( isset( $_FILES['keywordscsv'] ) ) {
		   $fdata = $_FILES['keywordscsv'];
		   
		   $count = count( $fdata['name'] );

		   for ($i = 0; $i < $count; ++$i ) {
		      $ext = pathinfo( $fdata['name'][$i], PATHINFO_EXTENSION);

		      if( in_array( strtolower( $ext ), $extok ) ) { 
		         $imagem_nome = uniqid().'.'.$ext;

		         $temp_name = $fdata['tmp_name'][$i];

		         move_uploaded_file($temp_name, $uploads_dir.$imagem_nome );
		         $img[] = $imagem_nome;
		      }
		   }
		}

		$arquivo = $uploads_dir.$imagem_nome;
		
		$ler_arquivo = fopen($arquivo,'r');

		if ($ler_arquivo) 
		{
			
	 		function multiexplode ($delimiters,$string) 
	 		{
    
					    $ready = str_replace($delimiters, $delimiters[0], $string);
					    $launch = explode($delimiters[0], $ready);
					    return  $launch;
			}
			if($_POST['keywordtext'])
			{
				$query_parts[] = "('".$_POST['keywordtext']."')";
				$query_parts2[] = "('".$_POST['keywordtext']."')";

				$string = implode(' OR MATCH(field_data_field_id_main_id.field_id_main_id_value) AGAINST ', str_replace(" ", "", $query_parts));
					
				$string2 = implode(' OR MATCH(field_data_field_id_main_id_on.field_id_main_id_on_value) AGAINST ', str_replace(" ", "", $query_parts2));

				//print_r($string); exit();
			}
			else
			{
				
				while ($data = fgetcsv($ler_arquivo,",")) 
		 		{

		 			$result_explode =  $data;
		 			if(count($result_explode) == 1)
		 			{	

		 				$replaces_array = array(";", ",");
		 				$result_explode = multiexplode(array(",",";",":"),$result_explode[0]); ;
		 			}
		 			
		 			foreach ($result_explode as $val) {
				    	$query_parts[] = "('".$val."')";
				    	$query_parts2[] = "('".$val."')";
					}
					$string = implode(' OR MATCH(field_data_field_id_main_id.field_id_main_id_value) AGAINST ', str_replace(" ", "", $query_parts));
					
					$string2 = implode(' OR MATCH(field_data_field_id_main_id_on.field_id_main_id_on_value) AGAINST ', str_replace(" ", "", $query_parts2));
		 			
		 		}
			}
	 		
	 		//print_r($string); exit();
	 		$replaces = array(" ", "'", "%");
	 		$termo = implode(",", $query_parts);
	 		

			$count = count($_POST['check_post_date']);

			//$check_string = explode("/", $_POST['check_post_date'][0]);
			
			

			for ($i=0; $i < $count; $i++) { 
				$check_string = explode("/", $_POST['check_post_date'][$i]);
				
				$coluns_join .= $check_string[0];

				$inner_join .= $check_string[1]." ";
				
				
				//$left_join .= ' LEFT  JOIN field_data_'.$_POST['check_field_collection'][$i].' ON field_data_'.$_POST['check_field_collection'][$i].'.entity_id = node.nid ';
				
				//$coluns_join .= ' field_data_'.$_POST['check_field_collection'][$i].'.'.$_POST['check_field_collection'][$i].'_value, ';

				$keys_post[$_POST['check_field_collection'][$i]]  = array($_POST['check_field_collection'][$i] => $_POST['check_field_collection'][$i]);
			}
			// $ct = explode(" ", $point);

			// for ($i=0; $i < $count; $i++) { 
			
			// 	$var =  "v".$ct[$i];
				
			// }
			 
	 			$query = mysqli_query($mysqli, "SELECT node.title as node_title, 
	 										{$coluns_join}
											node.nid as node_nid,
											node.type as node_type,
											node.created as post_date,
									field_data_field_id_main_id.field_id_main_id_value as nome_principal, 
									field_data_field_id_main_id_on.field_id_main_id_on_value as nomes_alternativos
						        FROM  node 
						        LEFT JOIN field_collection_item on field_collection_item.item_id = node.nid
						        LEFT  JOIN field_data_field_position_collection ON field_data_field_position_collection.entity_id = field_collection_item.item_id 
						       	INNER JOIN field_data_field_id_main_id ON node.nid = field_data_field_id_main_id.entity_id 
INNER JOIN field_data_field_id_main_id_on ON field_data_field_id_main_id_on.entity_id = field_data_field_id_main_id.entity_id 
								
								 {$inner_join}
						        WHERE MATCH (field_data_field_id_main_id.field_id_main_id_value) AGAINST {$string2} 
						        OR MATCH (field_data_field_id_main_id_on.field_id_main_id_on_value) AGAINST {$string}
						        AND(( (node.status = '1') AND (node.type IN ('star')) )) GROUP BY node.nid ORDER BY node_title ASC LIMIT 100 OFFSET 0");

				$query2 = mysqli_query($mysqli, "SELECT node.title as node_title, 
	 										{$coluns_join}
											node.nid as node_nid,
											node.type as node_type,
											node.created as post_date,
									field_data_field_id_main_id.field_id_main_id_value as nome_principal, 
									field_data_field_id_main_id_on.field_id_main_id_on_value as nomes_alternativos
						        FROM  node 
						        LEFT JOIN field_collection_item on field_collection_item.item_id = node.nid
						        LEFT  JOIN field_data_field_position_collection ON field_data_field_position_collection.entity_id = field_collection_item.item_id 
						       	INNER JOIN field_data_field_id_main_id ON node.nid = field_data_field_id_main_id.entity_id 
INNER JOIN field_data_field_id_main_id_on ON field_data_field_id_main_id_on.entity_id = field_data_field_id_main_id.entity_id 
								
								 {$inner_join}
						        WHERE MATCH (field_data_field_id_main_id.field_id_main_id_value) AGAINST {$string2} 
						        OR MATCH (field_data_field_id_main_id_on.field_id_main_id_on_value) AGAINST {$string}
						        AND(( (node.status = '1') AND (node.type IN ('star')) )) GROUP BY node.nid ORDER BY node_title ASC LIMIT 100 OFFSET 0"); 		

						        

	 			
	 		$query_export = "SELECT node.title as node_title, 
	 										{$coluns_join}
											node.nid as node_nid,
											node.type as node_type,
											node.created as post_date,
									field_data_field_id_main_id.field_id_main_id_value as nome_principal, 
									field_data_field_id_main_id_on.field_id_main_id_on_value as nomes_alternativos
						        FROM  node 
						        LEFT JOIN field_collection_item on field_collection_item.item_id = node.nid
						        LEFT  JOIN field_data_field_position_collection ON field_data_field_position_collection.entity_id = field_collection_item.item_id 
						       	INNER JOIN field_data_field_id_main_id ON node.nid = field_data_field_id_main_id.entity_id 
INNER JOIN field_data_field_id_main_id_on ON field_data_field_id_main_id_on.entity_id = field_data_field_id_main_id.entity_id 
								
								 {$inner_join}
						        WHERE MATCH (field_data_field_id_main_id.field_id_main_id_value) AGAINST {$string2} 
						        OR MATCH (field_data_field_id_main_id_on.field_id_main_id_on_value) AGAINST {$string}
						        AND(( (node.status = '1') AND (node.type IN ('star')) ))  ORDER BY node_title ASC LIMIT 100 OFFSET 0";

						        $query_export2 = "SELECT node.title as node_title, 
	 										{$coluns_join}
											node.nid as node_nid,
											node.type as node_type,
											node.created as post_date,
									field_data_field_id_main_id.field_id_main_id_value as nome_principal, 
									field_data_field_id_main_id_on.field_id_main_id_on_value as nomes_alternativos
						        FROM  node 
						        LEFT JOIN field_collection_item on field_collection_item.item_id = node.nid
						        LEFT  JOIN field_data_field_position_collection ON field_data_field_position_collection.entity_id = field_collection_item.item_id 
						       	INNER JOIN field_data_field_id_main_id ON node.nid = field_data_field_id_main_id.entity_id 
INNER JOIN field_data_field_id_main_id_on ON field_data_field_id_main_id_on.entity_id = field_data_field_id_main_id.entity_id 
								
								 {$inner_join}
						        WHERE MATCH (field_data_field_id_main_id.field_id_main_id_value) AGAINST {$string2} 
						        OR MATCH (field_data_field_id_main_id_on.field_id_main_id_on_value) AGAINST {$string}
						        AND(( (node.status = '1') AND (node.type IN ('star')) ))  ORDER BY node_title ASC LIMIT 100 OFFSET 0";

	 		
		}
	} 
	else
	{
		$query = mysqli_query($mysqli,"SELECT node.title as node_title, 
							     node.nid as node_nid,
							     node.type as node_type,
											node.created as post_date,
						         field_data_field_id_main_id.field_id_main_id_value as nome_principal,
						         field_data_field_id_main_id_on.field_id_main_id_on_value as nomes_alternativos,
						         field_data_field_reference.field_reference_url as referencia
						        FROM  node 
						       	INNER JOIN  field_data_field_id_main_id ON node.nid = field_data_field_id_main_id.entity_id
						        INNER JOIN field_data_field_id_main_id_on ON field_data_field_id_main_id_on.entity_id = field_data_field_id_main_id.entity_id
						        /*referencia*/
								LEFT  JOIN field_data_field_reference ON field_data_field_reference.entity_id = node.nid
								/*referencia*/
						        WHERE (( (node.status = '1') AND (node.type IN ('star')) )) ORDER BY node_title ASC LIMIT 100 OFFSET 0");	
	}
	
?>
<?php header('Access-Control-Allow-Origin: *'); ?>
<!DOCTYPE html>
<html lang="en">
   <head>
   <meta charset="utf-8">
    <!--[if IE]><meta http-equiv="x-ua-compatible" content="IE=9" /><![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Bootstrap -->
    <link rel="stylesheet" type="text/css"  href="css/bootstrap.css">
    <link href="fileinput/fileinput.css" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="datatables/dataTables.bootstrap.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

    
    <script type="text/javascript" src="js/jquery.1.11.1.js"></script>
    <script type="text/javascript" src="js/bootstrap.js"></script>
    <script src="fileinput/fileinput.js"></script>
    <!-- DataTables -->
    <script src="datatables/jquery.dataTables.min.js"></script>
    <script src="datatables/dataTables.bootstrap.min.js"></script>
 </head>
	<body class="hold-transition skin-blue sidebar-mini">
	
		<div style="margin-top:50px" class="col-md-10 col-md-offset-1 col-sm-8 col-sm-offset-2">
                <form role="form" data-toggle="validator" method="post" enctype="multipart/form-data" action="" >
                	<div class="col-md-5 col-sm-12 col-lg-5" style="font-weight: bold">Select results to be returned from the query:</div>
                	
					<!-- inicio div pai blocos-->
					<div class="col-md-12 col-sm-12 col-lg-12 nopadding">
						<div class="col-lg-6 col-md-6 col-sm-12" style="margin-top: 2%;margin-bottom: 2%">
							<label style="float: left;padding-right: 1%">Target:</label>
							<input style="border:black 2px solid" class="col-lg-10 col-md-10 col-sm-12" type="text" name="keywordtext" onfocus="this.value='';"></input>
							<button style="margin-right: 7%;margin-top: 4%;border: 2px solid;background-color: white;padding-left: 5%;padding-right: 5%;" type="submit" name="btntext" class="pull-right">search</button>
						</div>

						<div class="col-lg-6 col-md-6 col-sm-12" style="margin-top: 2%;margin-bottom: 2%">
							<label style="float: left;padding-right: 1%">Upload your list of targets:</label>
                      		<input style="border: 2px solid black;background-color: white;"  name="keywordscsv[]" type="file" >
                      		<button style="margin-right: 7%;margin-top: 4%;border: 2px solid;background-color: white;padding-left: 5%;padding-right: 5%;" type="submit" name="btnfile"  class="pull-right">search</button>
						</div>
						<!---incio bloco 1 checbox -->
						<div class="col-md-6 col-lg-2 col-sm-6 nopadding" style="border: black 1px solid;background: rgba(154, 128, 154, 0.31);min-height: 129px;">
							<div style="margin: 0 auto;text-align: center"><label>IDENTIFIERS</label></div>
							<div  class="checkbox">
								<label style="font-size: 96%;font-weight: bold">
									Main Indentifier:
								</label>
								<input  type="checkbox" name="check_main" value="check_main" checked>
							</div>
							<div  class="checkbox">
								<label style="font-size: 83%;font-weight: bold">
									Others Indentifiers:
								</label>
								<input  type="checkbox" name="check_others" value="check_others">
							</div>
						</div>
						<!---fim bloco 1 checbox -->
						<!---incio bloco 2 checbox -->
						<div class="col-md-6 col-lg-3 col-sm-6 nopadding" style="border: black 1px solid;background: rgba(20, 50, 206, 0.18);min-height: 129px;">
						<div style="margin: 0 auto;text-align: center"><label>ASTROMETRY</label></div>
							<div class="col-md-12 col-sm-12 col-lg-12 nopadding2">
								<div  class="checkbox col-md-6 col-lg-6 col-sm-12 nopadding">
									<label style="font-size: 96%;font-weight: bold">
										Position(a,δ):
									</label>
									<input id="mycheckbox" type="checkbox" name="check_post_date[]" value="field_data_field_pos_a_icrs.field_pos_a_icrs_value,
				field_data_field_pos_d_icrs.field_pos_d_icrs_value,
				field_data_field_pos_p_alpha.field_pos_p_alpha_value,
				field_data_field_pos_p_delta.field_pos_p_delta_value,
				field_data_field_pos_i.field_pos_i_value,
				field_data_field_pos_b.field_pos_b_value,
				field_data_field_pos_id_p_i.field_pos_id_p_i_value,
				field_data_field_pos_id_p_b.field_pos_id_p_b_value,
				ref_pos.field_reference_title as ref_pos,
				rem_pos.field_remarks_value as rem_pos,/
LEFT   JOIN field_data_field_pos_a_icrs ON field_data_field_pos_a_icrs.entity_id = field_data_field_position_collection.field_position_collection_value
LEFT   JOIN field_data_field_pos_d_icrs ON field_data_field_pos_d_icrs.entity_id = field_data_field_position_collection.field_position_collection_value
LEFT   JOIN field_data_field_pos_p_alpha ON field_data_field_pos_p_alpha.entity_id = field_data_field_position_collection.field_position_collection_value
LEFT   JOIN field_data_field_pos_p_delta ON field_data_field_pos_p_delta.entity_id = field_data_field_position_collection.field_position_collection_value
LEFT   JOIN field_data_field_pos_i ON field_data_field_pos_i.entity_id = field_data_field_position_collection.field_position_collection_value
LEFT   JOIN field_data_field_pos_b ON field_data_field_pos_b.entity_id = field_data_field_position_collection.field_position_collection_value
LEFT   JOIN field_data_field_pos_id_p_i ON field_data_field_pos_id_p_i.entity_id = field_data_field_position_collection.field_position_collection_value
LEFT   JOIN field_data_field_pos_id_p_b ON field_data_field_pos_id_p_b.entity_id = field_data_field_position_collection.field_position_collection_value
LEFT JOIN  field_data_field_reference as ref_pos ON field_data_field_position_collection.field_position_collection_value = ref_pos.entity_id 
LEFT JOIN field_data_field_remarks as rem_pos ON field_data_field_position_collection.field_position_collection_value = rem_pos.entity_id">

								</div>
								<div  class="checkbox col-md-6 col-lg-6 col-sm-12 nopadding" >
									<label style="font-size: 83%;font-weight: bold">
										Epoch:
									</label>
									<input  id="mycheckbox2" type="checkbox" name="check_post_date[]" value="field_data_field_pos_epra.field_pos_epra_value,
				field_data_field_pos_edec.field_pos_edec_value,/LEFT   JOIN field_data_field_pos_epra ON field_data_field_pos_epra.entity_id = field_data_field_position_collection.field_position_collection_value
LEFT   JOIN field_data_field_pos_edec ON field_data_field_pos_edec.entity_id = field_data_field_position_collection.field_position_collection_value">
								</div>
							</div>
							<div class="col-md-12 col-sm-12 col-lg-12 nopadding">
								<div  class="checkbox col-md-6 col-lg-6 col-sm-12 nopadding" >
									<label style="font-size: 96%;font-weight: bold">
										Proper motion:
									</label>
									<input id="mycheckbox3" type="checkbox" name="check_post_date[]" value="field_data_field_pm_mi_alpha.field_pm_mi_alpha_value,
				field_data_field_pm_mi_delta.field_pm_mi_delta_value,
				field_data_field_pm_p_mi_delta.field_pm_p_mi_delta_value,
				field_data_field_pm_p_mi_alpha.field_pm_p_mi_alpha_value,
				field_data_field_pm_npm.field_pm_npm_value,
				ref_motion.field_reference_title as ref_motion,
				rem_motion.field_remarks_value as rem_motion,/LEFT  JOIN field_data_field_proper_motion_collection ON field_data_field_proper_motion_collection.entity_id = field_collection_item.item_id
LEFT   JOIN field_data_field_pm_mi_alpha ON field_data_field_pm_mi_alpha.entity_id = field_data_field_proper_motion_collection.field_proper_motion_collection_value
LEFT   JOIN field_data_field_pm_mi_delta ON field_data_field_pm_mi_delta.entity_id = field_data_field_proper_motion_collection.field_proper_motion_collection_value
LEFT   JOIN field_data_field_pm_p_mi_delta ON field_data_field_pm_p_mi_delta.entity_id = field_data_field_proper_motion_collection.field_proper_motion_collection_value
LEFT   JOIN field_data_field_pm_p_mi_alpha ON field_data_field_pm_p_mi_alpha.entity_id = field_data_field_proper_motion_collection.field_proper_motion_collection_value
LEFT   JOIN field_data_field_pm_npm ON field_data_field_pm_npm.entity_id = field_data_field_proper_motion_collection.field_proper_motion_collection_value
LEFT JOIN  field_data_field_reference as ref_motion ON field_data_field_proper_motion_collection.field_proper_motion_collection_value = ref_motion.entity_id 
LEFT JOIN field_data_field_remarks as rem_motion ON field_data_field_proper_motion_collection.field_proper_motion_collection_value = rem_motion.entity_id">

								</div>
								<div  class="checkbox col-md-6 col-lg-6 col-sm-12 nopadding" >
									<label style="font-size: 83%;font-weight: bold">
										Parallax:
									</label>
									<input  id="mycheckbox4" type="checkbox" name="check_post_date[]" value="field_data_field_px_pi.field_px_pi_value,
				field_data_field_px_p_pi.field_px_p_pi_value,field_data_field_reference.field_reference_title,
				field_data_field_remarks.field_remarks_value,field_data_field_field_px_type.field_field_px_type_value,/
LEFT JOIN field_data_field_parallax_collection ON field_data_field_parallax_collection.entity_id = node.nid
LEFT JOIN field_data_field_px_pi ON field_data_field_px_pi.entity_id = field_data_field_parallax_collection.field_parallax_collection_value
LEFT JOIN field_data_field_px_p_pi ON field_data_field_px_p_pi.entity_id = field_data_field_parallax_collection.field_parallax_collection_value
LEFT JOIN field_data_field_field_px_type ON field_data_field_field_px_type.entity_id = field_data_field_parallax_collection.field_parallax_collection_value
LEFT JOIN field_data_field_reference ON field_data_field_parallax_collection.field_parallax_collection_value = field_data_field_reference.entity_id
LEFT JOIN field_data_field_remarks ON field_data_field_parallax_collection.field_parallax_collection_value = field_data_field_remarks.entity_id">

								</div>
							</div>
						</div>
						<!---fim bloco 2 checbox -->
						<!---incio bloco 3 checbox -->
						<div class="col-md-6 col-lg-3 col-sm-6 nopadding" style="border: black 1px solid;background: rgba(75, 202, 97, 0.34);min-height: 129px;">
						<div style="margin: 0 auto;text-align: center"><label>PHOTOMETRY</label></div>
							<div class="col-md-12 col-sm-12 col-lg-12 nopadding2">
								<div  class="checkbox col-md-6 col-lg-6 col-sm-12 nopadding">
									<label style="font-size: 96%;font-weight: bold">
										U,B,V,R,I:
									</label>
									<input id="mycheckbox5" type="checkbox" name="check_post_date[]" value="field_data_field_field_p_op_u.field_field_p_op_u_value,
				field_data_field_field_p_op_b.field_field_p_op_b_value,
				field_data_field_field_p_op_v.field_field_p_op_v_value,
				field_data_field_field_p_op_rc.field_field_p_op_rc_value,
				field_data_field_field_p_op_ic.field_field_p_op_ic_value,
				field_data_field_field_p_op_p_u.field_field_p_op_p_u_value,
				field_data_field_field_p_op_p_b.field_field_p_op_p_b_value,
				field_data_field_field_p_op_p_v.field_field_p_op_p_v_value,
				field_data_field_field_p_op_p_rc.field_field_p_op_p_rc_value,
				field_data_field_field_p_op_p_ic.field_field_p_op_p_ic_value,
				ref_optic.field_reference_title as ref_optic,
				rem_optic.field_remarks_value as rem_optic,/LEFT  JOIN field_data_field_optic_photometry_collectio ON field_data_field_optic_photometry_collectio.entity_id = field_collection_item.item_id
LEFT   JOIN field_data_field_field_p_op_u ON field_data_field_field_p_op_u.entity_id = field_data_field_optic_photometry_collectio.field_optic_photometry_collectio_value
LEFT   JOIN field_data_field_field_p_op_b ON field_data_field_field_p_op_b.entity_id = field_data_field_optic_photometry_collectio.field_optic_photometry_collectio_value
LEFT   JOIN field_data_field_field_p_op_v ON field_data_field_field_p_op_v.entity_id = field_data_field_optic_photometry_collectio.field_optic_photometry_collectio_value
LEFT   JOIN field_data_field_field_p_op_rc ON field_data_field_field_p_op_rc.entity_id = field_data_field_optic_photometry_collectio.field_optic_photometry_collectio_value
LEFT   JOIN field_data_field_field_p_op_ic ON field_data_field_field_p_op_ic.entity_id = field_data_field_optic_photometry_collectio.field_optic_photometry_collectio_value
LEFT   JOIN field_data_field_field_p_op_p_b ON field_data_field_field_p_op_p_b.entity_id = field_data_field_optic_photometry_collectio.field_optic_photometry_collectio_value
LEFT   JOIN field_data_field_field_p_op_p_u ON field_data_field_field_p_op_p_u.entity_id = field_data_field_optic_photometry_collectio.field_optic_photometry_collectio_value
LEFT   JOIN field_data_field_field_p_op_p_v ON field_data_field_field_p_op_p_v.entity_id = field_data_field_optic_photometry_collectio.field_optic_photometry_collectio_value
LEFT   JOIN field_data_field_field_p_op_p_rc ON field_data_field_field_p_op_p_rc.entity_id = field_data_field_optic_photometry_collectio.field_optic_photometry_collectio_value
LEFT   JOIN field_data_field_field_p_op_p_ic ON field_data_field_field_p_op_p_ic.entity_id = field_data_field_optic_photometry_collectio.field_optic_photometry_collectio_value
LEFT JOIN  field_data_field_reference as ref_optic ON field_data_field_optic_photometry_collectio.field_optic_photometry_collectio_value = ref_optic.entity_id 
LEFT JOIN field_data_field_remarks as rem_optic ON field_data_field_optic_photometry_collectio.field_optic_photometry_collectio_value = rem_optic.entity_id">
								</div>
								<div  class="checkbox col-md-6 col-lg-6 col-sm-12 nopadding" >
									<label style="font-size: 83%;font-weight: bold">
										J,H,K:
									</label>
									<input id="mycheckbox6" type="checkbox" name="check_post_date[]" value="field_data_field_field_p_ir_j2mass.field_field_p_ir_j2mass_value,
				field_data_field_field_p_ir_h2mass.field_field_p_ir_h2mass_value,
				field_data_field_field_p_ir_ksmass.field_field_p_ir_ksmass_value,
				field_data_field_field_p_ir_p_j2mass.field_field_p_ir_p_j2mass_value,
				field_data_field_field_p_ir_p_h2mass.field_field_p_ir_p_h2mass_value,
				field_data_field_field_p_ir_p_ks2mass.field_field_p_ir_p_ks2mass_value,
				ref_ir.field_reference_title as ref_ir,
				rem_ir.field_remarks_value as rem_ir,/LEFT  JOIN field_data_field_ir_photometry_collection ON field_data_field_ir_photometry_collection.entity_id = field_collection_item.item_id
LEFT   JOIN field_data_field_field_p_ir_j2mass ON field_data_field_field_p_ir_j2mass.entity_id = field_data_field_ir_photometry_collection.field_ir_photometry_collection_value
LEFT   JOIN field_data_field_field_p_ir_h2mass ON field_data_field_field_p_ir_h2mass.entity_id = field_data_field_ir_photometry_collection.field_ir_photometry_collection_value
LEFT   JOIN field_data_field_field_p_ir_ksmass ON field_data_field_field_p_ir_ksmass.entity_id = field_data_field_ir_photometry_collection.field_ir_photometry_collection_value
LEFT   JOIN field_data_field_field_p_ir_p_j2mass ON field_data_field_field_p_ir_p_j2mass.entity_id = field_data_field_ir_photometry_collection.field_ir_photometry_collection_value
LEFT   JOIN field_data_field_field_p_ir_p_h2mass ON field_data_field_field_p_ir_p_h2mass.entity_id = field_data_field_ir_photometry_collection.field_ir_photometry_collection_value
LEFT   JOIN field_data_field_field_p_ir_p_ks2mass ON field_data_field_field_p_ir_p_ks2mass.entity_id = field_data_field_ir_photometry_collection.field_ir_photometry_collection_value
LEFT JOIN  field_data_field_reference as ref_ir ON field_data_field_ir_photometry_collection.field_ir_photometry_collection_value = ref_ir.entity_id 
LEFT JOIN field_data_field_remarks as rem_ir ON field_data_field_ir_photometry_collection.field_ir_photometry_collection_value = rem_ir.entity_id">

								</div>
								<div  class="checkbox col-md-6 col-lg-6 col-sm-12 nopadding">
								<label style="font-size: 75%;font-weight: bold">
									Gaia photometry:
								</label>
								<input id="mycheckbox7" type="checkbox" name="check_post_date[]" value="field_data_field_gaia_g.field_gaia_g_value,
				field_data_field_gaia_bp.field_gaia_bp_value,
				field_data_field_gaia_rp.field_gaia_rp_value,
				field_data_field_gaia_pg.field_gaia_pg_value,
				field_data_field_gaia_pbp.field_gaia_pbp_value,
				field_data_field_gaia_prp.field_gaia_prp_value,
				ref_gaia.field_reference_title as ref_gaia,
				rem_gaia.field_remarks_value as rem_gaia,/LEFT  JOIN field_data_field_gaia_photometry ON field_data_field_gaia_photometry.entity_id = field_collection_item.item_id
LEFT   JOIN field_data_field_gaia_g ON field_data_field_gaia_g.entity_id = field_data_field_gaia_photometry.field_gaia_photometry_value
LEFT   JOIN field_data_field_gaia_bp ON field_data_field_gaia_bp.entity_id = field_data_field_gaia_photometry.field_gaia_photometry_value
LEFT   JOIN field_data_field_gaia_rp ON field_data_field_gaia_rp.entity_id = field_data_field_gaia_photometry.field_gaia_photometry_value
LEFT   JOIN field_data_field_gaia_pg ON field_data_field_gaia_pg.entity_id = field_data_field_gaia_photometry.field_gaia_photometry_value
LEFT   JOIN field_data_field_gaia_pbp ON field_data_field_gaia_pbp.entity_id = field_data_field_gaia_photometry.field_gaia_photometry_value
LEFT   JOIN field_data_field_gaia_prp ON field_data_field_gaia_prp.entity_id = field_data_field_gaia_photometry.field_gaia_photometry_value
LEFT JOIN  field_data_field_reference as ref_gaia ON field_data_field_gaia_photometry.field_gaia_photometry_value = ref_gaia.entity_id 
LEFT JOIN field_data_field_remarks as rem_gaia ON field_data_field_gaia_photometry.field_gaia_photometry_value = rem_gaia.entity_id">

							</div>
							<div  class="checkbox col-md-6 col-lg-6 col-sm-12 nopadding">
								<label style="font-size: 70%;font-weight: bold">
									WISE photometry:
								</label>
								<input id="mycheckbox8" type="checkbox" name="check_post_date[]" value="field_data_field_field_field_p_ir_catalog.field_field_field_p_ir_catalog_value,
				field_data_field_field_p_ir_w1_wise.field_field_p_ir_w1_wise_value,
				field_data_field_field_p_ir_w2_wise.field_field_p_ir_w2_wise_value,
				field_data_field_field_p_ir_w3_wise.field_field_p_ir_w3_wise_value,
				field_data_field_field_p_ir_w4_wise.field_field_p_ir_w4_wise_value,
				field_data_field_field_p_ir_p_w1_wise.field_field_p_ir_p_w1_wise_value,
				field_data_field_field_p_ir_p_w2_wise.field_field_p_ir_p_w2_wise_value,
				field_data_field_field_p_ir_p_w3_wise.field_field_p_ir_p_w3_wise_value,
				field_data_field_field_p_ir_p_w4_wise.field_field_p_ir_p_w4_wise_value,
				field_data_field_field_p_ir_flag_2.field_field_p_ir_flag_2_value,
				ref_wise.field_reference_title as ref_wise,
				rem_wise.field_remarks_value as rem_wise,/LEFT  JOIN field_data_field_wise_photometry_collection ON field_data_field_wise_photometry_collection.entity_id = field_collection_item.item_id
LEFT   JOIN field_data_field_field_field_p_ir_catalog ON field_data_field_field_field_p_ir_catalog.entity_id = field_data_field_wise_photometry_collection.field_wise_photometry_collection_value
LEFT   JOIN field_data_field_field_p_ir_w1_wise ON field_data_field_field_p_ir_w1_wise.entity_id = field_data_field_wise_photometry_collection.field_wise_photometry_collection_value
LEFT   JOIN field_data_field_field_p_ir_w2_wise ON field_data_field_field_p_ir_w2_wise.entity_id = field_data_field_wise_photometry_collection.field_wise_photometry_collection_value
LEFT   JOIN field_data_field_field_p_ir_w3_wise ON field_data_field_field_p_ir_w3_wise.entity_id = field_data_field_wise_photometry_collection.field_wise_photometry_collection_value
LEFT   JOIN field_data_field_field_p_ir_w4_wise ON field_data_field_field_p_ir_w4_wise.entity_id = field_data_field_wise_photometry_collection.field_wise_photometry_collection_value
LEFT   JOIN field_data_field_field_p_ir_p_w1_wise ON field_data_field_field_p_ir_p_w1_wise.entity_id = field_data_field_wise_photometry_collection.field_wise_photometry_collection_value
LEFT   JOIN field_data_field_field_p_ir_p_w2_wise ON field_data_field_field_p_ir_p_w2_wise.entity_id = field_data_field_wise_photometry_collection.field_wise_photometry_collection_value
LEFT   JOIN field_data_field_field_p_ir_p_w3_wise ON field_data_field_field_p_ir_p_w3_wise.entity_id = field_data_field_wise_photometry_collection.field_wise_photometry_collection_value
LEFT   JOIN field_data_field_field_p_ir_p_w4_wise ON field_data_field_field_p_ir_p_w4_wise.entity_id = field_data_field_wise_photometry_collection.field_wise_photometry_collection_value
LEFT   JOIN field_data_field_field_p_ir_flag_2 ON field_data_field_field_p_ir_flag_2.entity_id = field_data_field_wise_photometry_collection.field_wise_photometry_collection_value
LEFT JOIN  field_data_field_reference as ref_wise ON field_data_field_wise_photometry_collection.field_wise_photometry_collection_value = ref_wise.entity_id 
LEFT JOIN field_data_field_remarks as rem_wise ON field_data_field_wise_photometry_collection.field_wise_photometry_collection_value = rem_wise.entity_id">

							</div>
							</div>
							
						</div>
						<!---fim bloco 3 checbox -->
						<!---incio bloco 4 checbox -->
						<div class="col-md-6 col-lg-4 col-sm-6 nopadding" style="border: black 1px solid;background: rgba(255, 192, 203, 0.41);min-height: 129px;">
						<div style="margin: 0 auto;text-align: center"><label>SPECTROSCOPY</label></div>
							<div class="col-md-12 col-sm-12 col-lg-12 nopadding2">
								<div  class="checkbox col-md-6 col-lg-6 col-sm-12 nopadding">
									<label style="font-size: 96%;font-weight: bold">
										Radial velocity:
									</label>
									<input id="mycheckbox9" type="checkbox" name="check_post_date[]" value="field_data_field_field_s_rad_v_vr.field_field_s_rad_v_vr_value,
				field_data_field_field_s_rad_p_vr.field_field_s_rad_p_vr_value,
				ref_velocity.field_reference_title as ref_velocity,
				rem_velocity.field_remarks_value as rem_velocity,/LEFT  JOIN field_data_field_radial_velocity_collection ON field_data_field_radial_velocity_collection.entity_id = field_collection_item.item_id
LEFT   JOIN field_data_field_field_s_rad_v_vr ON field_data_field_field_s_rad_v_vr.entity_id = field_data_field_radial_velocity_collection.field_radial_velocity_collection_value
LEFT   JOIN field_data_field_field_s_rad_p_vr ON field_data_field_field_s_rad_p_vr.entity_id = field_data_field_radial_velocity_collection.field_radial_velocity_collection_value
LEFT JOIN  field_data_field_reference as ref_velocity ON field_data_field_radial_velocity_collection.field_radial_velocity_collection_value = ref_velocity.entity_id 
LEFT JOIN field_data_field_remarks as rem_velocity ON field_data_field_radial_velocity_collection.field_radial_velocity_collection_value = rem_velocity.entity_id">

								</div>
								<div  class="checkbox col-md-6 col-lg-6 col-sm-12 nopadding" >
									<label style="font-size: 83%;font-weight: bold">
										Rotational velocity:
									</label>
									<input id="mycheckbox10" type="checkbox" name="check_post_date[]" value="field_data_field_s_rot_vsin.field_s_rot_vsin_value,
				field_data_field_s_rot_p_vsin.field_s_rot_p_vsin_value,
				ref_rotat.field_reference_title as ref_rotat,
				rem_rotat.field_remarks_value as rem_rotat,/LEFT  JOIN field_data_field_rotat_velocity_collection ON field_data_field_rotat_velocity_collection.entity_id = field_collection_item.item_id
LEFT   JOIN field_data_field_s_rot_vsin ON field_data_field_s_rot_vsin.entity_id = field_data_field_rotat_velocity_collection.field_rotat_velocity_collection_value
LEFT   JOIN field_data_field_s_rot_p_vsin ON field_data_field_s_rot_p_vsin.entity_id = field_data_field_rotat_velocity_collection.field_rotat_velocity_collection_value
LEFT JOIN  field_data_field_reference as ref_rotat ON field_data_field_rotat_velocity_collection.field_rotat_velocity_collection_value = ref_rotat.entity_id 
LEFT JOIN field_data_field_remarks as rem_rotat ON field_data_field_rotat_velocity_collection.field_rotat_velocity_collection_value = rem_rotat.entity_id">

								</div>
							</div>
							<div class="col-md-12 col-sm-12 col-lg-12 nopadding">
								<div  class="checkbox col-md-6 col-lg-6 col-sm-12 nopadding" >
									<label style="font-size: 96%;font-weight: bold">
										Spectral type:
									</label>
									<input id="mycheckbox11" type="checkbox" name="check_post_date[]" value="field_data_field_s_spec_sp_select.field_s_spec_sp_select_value,
				field_data_field_s_spec_p_sp.field_s_spec_p_sp_value,
				ref_spec.field_reference_title as ref_spec,
				rem_spec.field_remarks_value as rem_spec,/LEFT  JOIN field_data_field_spectral_type_collection ON field_data_field_spectral_type_collection.entity_id = field_collection_item.item_id
LEFT   JOIN field_data_field_s_spec_sp_select ON field_data_field_s_spec_sp_select.entity_id = field_data_field_spectral_type_collection.field_spectral_type_collection_value
LEFT   JOIN field_data_field_s_spec_p_sp ON field_data_field_s_spec_p_sp.entity_id = field_data_field_spectral_type_collection.field_spectral_type_collection_value
LEFT JOIN  field_data_field_reference as ref_spec ON field_data_field_spectral_type_collection.field_spectral_type_collection_value = ref_spec.entity_id 
LEFT JOIN field_data_field_remarks as rem_spec ON field_data_field_spectral_type_collection.field_spectral_type_collection_value = rem_spec.entity_id">

								</div>
								<div  class="checkbox col-md-6 col-lg-6 col-sm-12 nopadding" >
									<label style="font-size: 83%;font-weight: bold">
										Temperature:
									</label>
									<input id="mycheckbox12" type="checkbox" name="check_post_date[]" value="field_data_field_s_temp_teff.field_s_temp_teff_value,
				field_data_field_s_temp_p_teff.field_s_temp_p_teff_value,
				ref_temperature.field_reference_title as ref_temperature,
				rem_temperature.field_remarks_value as rem_temperature,/LEFT  JOIN field_data_field_temperature_collection ON field_data_field_temperature_collection.entity_id = field_collection_item.item_id
LEFT   JOIN field_data_field_s_temp_teff ON field_data_field_s_temp_teff.entity_id = field_data_field_temperature_collection.field_temperature_collection_value
LEFT   JOIN field_data_field_s_temp_p_teff ON field_data_field_s_temp_p_teff.entity_id = field_data_field_temperature_collection.field_temperature_collection_value
LEFT JOIN  field_data_field_reference as ref_temperature ON field_data_field_temperature_collection.field_temperature_collection_value = ref_temperature.entity_id 
LEFT JOIN field_data_field_remarks as rem_temperature ON field_data_field_temperature_collection.field_temperature_collection_value = rem_temperature.entity_id">

								</div>
							</div>
							<div class="col-md-12 col-sm-12 col-lg-12 nopadding">
								<div  class="checkbox col-md-4 col-lg-4 col-sm-12 nopadding" >
									<label style="font-size: 96%;font-weight: bold">
										Li,Ha:
									</label>
									<input  id="mycheckbox13" type="checkbox" name="check_post_date[]" value="	field_data_field_s_lih_ew.field_s_lih_ew_value,
				field_data_field_s_lih_pew.field_s_lih_pew_value,
				field_data_field_s_lih_ewha.field_s_lih_ewha_value,
				field_data_field_s_lih_pewha.field_s_lih_pewha_value,
				ref_li_ha.field_reference_title as ref_li_ha,
				rem_li_ha.field_remarks_value as rem_li_ha,/LEFT  JOIN field_data_field_li_h_collection ON field_data_field_li_h_collection.entity_id = field_collection_item.item_id
LEFT   JOIN field_data_field_s_lih_ew ON field_data_field_s_lih_ew.entity_id = field_data_field_li_h_collection.field_li_h_collection_value
LEFT   JOIN field_data_field_s_lih_pew ON field_data_field_s_lih_pew.entity_id = field_data_field_li_h_collection.field_li_h_collection_value
LEFT   JOIN field_data_field_s_lih_ewha ON field_data_field_s_lih_ewha.entity_id = field_data_field_li_h_collection.field_li_h_collection_value
LEFT   JOIN field_data_field_s_lih_pewha ON field_data_field_s_lih_pewha.entity_id = field_data_field_li_h_collection.field_li_h_collection_value
LEFT JOIN  field_data_field_reference as ref_li_ha ON field_data_field_li_h_collection.field_li_h_collection_value = ref_li_ha.entity_id 
LEFT JOIN field_data_field_remarks as rem_li_ha ON field_data_field_li_h_collection.field_li_h_collection_value = rem_li_ha.entity_id">


								</div>
								<div  class="checkbox col-md-4 col-lg-4 col-sm-12 nopadding" >
									<label style="font-size: 83%;font-weight: bold">
										YSO class:
									</label>
									<input id="mycheckbox14" type="checkbox" name="check_post_date[]" value="field_data_field_field_exyso_class.field_field_exyso_class_value,
				field_data_field_ex_yso_link_reference.field_ex_yso_link_reference_title,
				field_data_field_field_ex_yso_pms_status.field_field_ex_yso_pms_status_value,
				ref_yso.field_reference_title as ref_yso,
				rem_yso.field_remarks_value as rem_yso,/LEFT  JOIN field_data_field_yso_collection ON field_data_field_yso_collection.entity_id = field_collection_item.item_id
LEFT   JOIN field_data_field_field_exyso_class ON field_data_field_field_exyso_class.entity_id = field_data_field_yso_collection.field_yso_collection_value
LEFT   JOIN field_data_field_ex_yso_link_reference ON field_data_field_ex_yso_link_reference.entity_id = field_data_field_yso_collection.field_yso_collection_value
LEFT   JOIN field_data_field_field_ex_yso_pms_status ON field_data_field_field_ex_yso_pms_status.entity_id = field_data_field_yso_collection.field_yso_collection_value
LEFT JOIN  field_data_field_reference as ref_yso ON field_data_field_yso_collection.field_yso_collection_value = ref_yso.entity_id 
LEFT JOIN field_data_field_remarks as rem_yso ON field_data_field_yso_collection.field_yso_collection_value = rem_yso.entity_id">

								</div>
								<div  class="checkbox col-md-4 col-lg-4 col-sm-12 nopadding" >
									<label style="font-size: 83%;font-weight: bold">
										Multiplicity:
									</label>
									<input id="mycheckbox15" type="checkbox" name="check_post_date[]" value="">
								</div>
							</div>
						</div>
						<!---fim bloco 4 checbox -->
						
						
					</div>
					<!-- fim div pai blocos-->
					<div class="row nopadding">
                	</form>
		                <?php if($_FILES) { ?>
		                	
		                	<form action="exportcsv.inc.php" method="post">
		                	<input type="hidden" value="<?php echo $string;?>" name="string" ></input>
		                	<input type="hidden" value="<?php echo $string2;?>" name="string2" ></input>
		                	<input type="hidden" value="<?php echo $query_export2;?>" name="query2" ></input>
		                	<input type="hidden" value="<?php echo $termo;?>" name="termo" ></input>
		                	<input type="hidden" value="<?php echo $_POST['check_star_name'][0];?>" name="star_name" ></input>
		                	<input type="hidden" value="<?php echo $_POST['check_type'][0];?>" name="type" ></input>
		                	<input type="hidden" value="<?php echo $_POST['check_others'][0];?>" name="check_others" ></input>

		                	<!-- positon -->
		                	
		                <?php	for ($i=0; $i < $count; $i++) { ?>

		                	<input type='hidden' value='<?php echo $_POST['check_field_collection'][$i] ?>' name='<?php echo $_POST['check_field_collection'][$i] ?>' ></input>'
						
						<?php } ?>

			                    <button type="submit" style="margin-right: 7%;margin-top: 4%;border: 2px solid;background-color: white;" class="pull-left">Export Results</button>
	                </form>
	                
		                	
		                	<form action="exportcsv.inc.php" method="post">
		                	<input type="hidden" value="<?php echo $string;?>" name="string" ></input>
		                	<input type="hidden" value="<?php echo $string2;?>" name="string2" ></input>
		                	<input type="hidden" value="<?php echo $query_export2;?>" name="query2" ></input>
		                	<input type="hidden" value="<?php echo $termo;?>" name="termo" ></input>
		                	<input type="hidden" value="<?php echo $_POST['check_star_name'][0];?>" name="star_name" ></input>
		                	<input type="hidden" value="<?php echo $_POST['check_type'][0];?>" name="type" ></input>
		                	<input type="hidden" value="<?php echo $_POST['check_others'][0];?>" name="check_others" ></input>
		                	<input type="hidden" value="prev" name="prev" ></input>
		                	<!-- positon -->
		                	
		                <?php	for ($i=0; $i < $count; $i++) { ?>

		                	<input type='hidden' value='<?php echo $_POST['check_field_collection'][$i] ?>' name='<?php echo $_POST['check_field_collection'][$i] ?>' ></input>'
						
						<?php } ?>

			                    <button type="submit" style="margin-right: 7%;margin-top: 4%;border: 2px solid;background-color: white;" class="pull-right">Export Result Preview</button>
	                </form>
	                </div>
	                
	               
	                <?php } ?>
	                <?php @$linha = mysqli_fetch_array($query2); ?>
                	<label style="float: left;padding-right: 1%;margin-top: 5%">Query Results Preview:</label>
	                <table id="example1" class="table table-bordered table-striped">
	                    <thead>
	                    	<tr>
	                    		<?php    if($_FILES) { ?>
		                      	<th>Termos(s)</th>
		                      	<?php } ?>
		                      	<?php if($_POST['check_star_name'][0]){ ?>
	 							<th>STAR NAME</th>
	 							<?php } ?>
	 							<?php if($_POST['check_main'][0]){ ?>
		                        <th>MAIN IDENTIFIER</th>
		                        <?php } ?>
		                        <?php if($_POST['check_others'][0]){ ?>
		                        <th>OTHERS IDENTIFIERS</th>
		                        <?php } ?>

			                    <!--position -->
		                        <?php if($linha['field_pos_a_icrs_value']){ ?>
		                        <th>α (ICRS)</th>
		                        <th>δ (ICRS)</th>
		                        <th>σα</th>
		                        <th>σδ</th>
		                        <th>I (ICRS)</th>
		                        <th>b (ICRS)</th>
		                        <th>σI</th>
		                        <th>σb</th>
		                        <th>Position Reference</th>
		                        <?php } ?>
		                        <?php if($linha['field_pos_epra_value']){ ?>
		                        <th>Epoch (α or I)</th>
		                        <th>Epoch (δ or I)</th>
		                        <?php } ?>
		                        <!--position -->

		                        <!--proper motion-->
		                        <?php if($linha['field_pm_mi_alpha_value']){ ?>
		                        <th>μαcosδ</th>
		                        <th>μδ</th>
		                        <th>σμαcosδ </th>
		                        <th>σμδ</th>
		                        <th>Number of Points </th>
		                        <th>Proper Motion Reference </th>
		                        <?php } ?>
		                        <!--proper motion -->

		                        <!--parallax-->
		                        <?php if($linha['field_px_pi_value']){ ?>
		                        <th>π</th>
		                        <th>σπ</th>
		                        <th>Parallax Type </th>
		                        <th>Parallax Reference </th>
		                        <?php } ?>
		                        <!--parallax -->

		                        <!--ir photometry -->
		                        <?php  if($linha['field_field_p_ir_j2mass_value']){ ?>
		                        <th>J</th>
		                        <th>H</th>
		                        <th>K</th>
		                        <th>σJ</th>
		                        <th>σH</th>
		                        <th>σK</th>
		                        <th>J,H,K Reference</th>
		                        <?php } ?>
		                        <!--ir photometry -->

		                        <!--gaia photometry -->
		                        <?php  if($linha['field_gaia_g_value']){ ?>
		                        <th>G</th>
		                        <th>BP</th>
		                        <th>RP</th>
		                        <th>σG</th>
		                        <th>σBP</th>
		                        <th>σRP</th>
		                        <th>Gaia Reference</th>
		                        <?php } ?>
		                        <!--gaia photometry -->
		                        <!--OPTIC -->
		                        <?php  if($linha['field_field_p_op_u_value']){ ?>
		                        <th>U</th>
		                        <th>B</th>
		                        <th>V</th>
		                        <th>Rc</th>
		                        <th>Ic</th>
		                        <th>σU</th>
		                        <th>σB</th>
		                        <th>σV</th>
		                        <th>σRc</th>
		                        <th>σIc</th>
		                        <th>Optic Reference</th>
		                        <?php } ?>
		                        <!--OPTIC -->

		                        <!--wise photometry-->
		                        <?php  if($linha['field_field_p_ir_w1_wise_value']){ ?>
		                        <th>W1(3.4µm)</th>
		                        <th>W2(4.6µm)</th>
		                        <th>W3(12µm)</th>
		                        <th>W4(22µm)</th>
		                        <th>σW1</th>
		                        <th>σW2</th>
		                        <th>σW3</th>
		                        <th>σW4</th>
		                        <th>FLAG</th>
		                        <th>Wise Reference</th>
		                        <?php } ?>
		                        <!--wise photometry -->

		                        <!--radial velocity-->
		                        <?php  if($linha['field_field_s_rad_v_vr_value']){ ?>
		                        <th>Vr</th>
		                        <th>σVr</th>
		                        <th>Radial Reference</th>
		                        <?php } ?>
		                        <!--radial velocity -->

		                        <!--rotat velocity-->
		                        <?php  if($linha['field_s_rot_vsin_value']){ ?>
		                        <th>Vsin(i)</th>
		                        <th>σVsin(i)</th>
		                        <th>Rotat Velocity</th>
		                        <?php } ?>
		                        <!--rotat velocity -->

		                        <!--spactral type-->
		                        <?php  if($linha['field_s_spec_p_sp_value']){ ?>
		                        <th>Spetral Type</th>
		                        <th>Uncertainty</th>
		                        <th>Spectral Reference</th>
		                        <?php } ?>
		                        <!--spactral type -->

		                        <!--temperature-->
		                        <?php  if($linha['field_s_temp_p_teff_value']){ ?>
		                        <th>Teff</th>
		                        <th>σTeff</th>
		                        <th>Temperature Reference</th>
		                        <?php } ?>
		                        <!--temperature -->

		                        <!--Li,Hα -->
		                        <?php  if($linha['field_s_lih_ew_value']){ ?>
		                        <th>EW(Li)</th>
		                        <th>σEW(Li)</th>
		                        <th>EW(Hα)</th>
		                        <th>σEW(Hα)</th>
		                        <th>Li,Ha Reference</th>
		                        <?php } ?>
		                        <!--Li,Hα -->
		                        <!--yso-->
		                        <?php  if($linha['field_field_exyso_class_value']){ ?>
		                        <th>SED Class</th>
		                        <th>Object Type</th>
		                        <th>YSO Refrence</th>
		                        <?php } ?>
		                        <!--yso -->
	                    	</tr>
	                    </thead>
	                    <tbody>
	                    	<?php 
	                    		while ($row = mysqli_fetch_array($query)) { 
	                    	?>
	                      <tr>
	                       <?php if($_FILES) { ?>
	                      	<td><?php echo str_replace($replaces, "", $termo); ?></td>
	                       <?php } ?>
	                       <?php if($_POST['check_star_name'][0]){ ?>
	                       <td><?php echo $row['node_title']; ?></td>
	                       <?php } ?>
	                       <?php if($_POST['check_main'][0]){ ?>
	                        <td><?php echo $row['nome_principal']; ?></td>
	                        <?php } ?>
	                        <?php if($_POST['check_others'][0]){ ?>
	                        <td><?php echo $row['nomes_alternativos']; ?></td>
	                        <?php } ?>
	                        <?php if($_POST['check_type'][0]){ ?>
	                        <td><?php echo $row['node_type']; ?></td>
	                        <?php } ?>

	                        <!--position -->
	                        <?php if($row['field_pos_a_icrs_value']){ ?>
	                        <td><?php echo $row['field_pos_a_icrs_value']; ?></td>
	                        <td><?php echo $row['field_pos_d_icrs_value']; ?></td>
	                        <td><?php echo $row['field_pos_p_alpha_value']; ?></td>
	                        <td><?php echo $row['field_pos_p_delta_value']; ?></td>
	                        <td><?php echo $row['field_pos_i_value']; ?></td>
	                        <td><?php echo $row['field_pos_b_value']; ?></td>
	                        <td><?php echo $row['field_pos_id_p_i_value']; ?></td>
	                        <td><?php echo $row['field_pos_id_p_b_value']; ?></td>
	                        <td><?php echo $row['ref_pos']; ?></td>
	                        <?php } ?>
	                        <?php if($row['field_pos_epra_value']){ ?>
	                         <td><?php echo $row['field_pos_epra_value']; ?></td>
	                        <td><?php echo $row['field_pos_edec_value']; ?></td>
	                        <?php } ?>
	                        <!--position -->

	                        <!--proper motion -->
	                        <?php if($row['field_pm_mi_alpha_value']){ ?>
	                        <td><?php echo $row['field_pm_mi_alpha_value']; ?></td>
	                        <td><?php echo  $row['field_pm_mi_delta_value']; ?></td>
	                        <td><?php echo $row['field_pm_p_mi_delta_value']; ?></td>
	                        <td><?php echo $row['field_pm_p_mi_alpha_value']; ?></td>
	                        <td><?php echo  $row['field_pm_npm_value']; ?></td>
	                        <td><?php echo  $row['ref_motion']; ?></td>
	                        <?php } ?>
	                        <!--proper motion -->

	                        <!--parallax -->
	                        <?php if($row['field_px_pi_value']){ ?>
	                        <td><?php echo $row['field_px_pi_value']; ?></td>
	                        <td><?php echo  $row['field_px_p_pi_value']; ?></td>
	                        <td><?php echo $row['field_field_px_type_value']; ?></td>
	                        <td><?php echo $row['field_reference_title']; ?></td>
	                        <?php } ?>
	                        <!--parallax -->

	                        <!--ir_photometry -->
	                        <?php if($row['field_field_p_ir_j2mass_value']){ ?> 
	                        <td><?php echo $row['field_field_p_ir_j2mass_value']; ?></td>
	                        <td><?php echo  $row['field_field_p_ir_h2mass_value']; ?></td>
	                        <td><?php echo $row['field_field_p_ir_ksmass_value']; ?></td>
	                        <td><?php echo $row['field_field_p_ir_p_j2mass_value']; ?></td>
	                        <td><?php echo $row['field_field_p_ir_p_h2mass_value']; ?></td>
	                        <td><?php echo $row['field_field_p_ir_p_ks2mass_value']; ?></td>
	                        <td><?php echo $row['ref_ir']; ?></td>
	                        <?php } ?>
	                        <!--ir_photometry -->

	                        <!--gaia_photometry -->
	                        <?php if($row['field_gaia_g_value']){ ?> 
	                        <td><?php echo $row['field_gaia_g_value']; ?></td>
	                        <td><?php echo  $row['field_gaia_bp_value']; ?></td>
	                        <td><?php echo $row['field_gaia_rp_value']; ?></td>
	                        <td><?php echo $row['field_gaia_pg_value']; ?></td>
	                        <td><?php echo $row['field_gaia_pbp_value']; ?></td>
	                        <td><?php echo $row['field_gaia_prp_value']; ?></td>
	                        <td><?php echo $row['ref_gaia']; ?></td>
	                        <?php } ?>
	                        <!--gaia_photometry -->

	                         <!--optic_photometry--> 
	                        <?php if($row['field_field_p_op_u_value']){ ?> 
	                        <td><?php echo $row['field_field_p_op_u_value']; ?></td>
	                        <td><?php echo  $row['field_field_p_op_b_value']; ?></td>
	                        <td><?php echo $row['field_field_p_op_v_value']; ?></td>
	                        <td><?php echo $row['field_field_p_op_rc_value']; ?></td>
	                        <td><?php echo $row['field_field_p_op_ic_value']; ?></td>
	                        <td><?php echo $row['field_field_p_op_p_u_value']; ?></td>
	                        <td><?php echo $row['field_field_p_op_p_b_value']; ?></td>
	                        <td><?php echo $row['field_field_p_op_p_v_value']; ?></td>
	                        <td><?php echo $row['field_field_p_op_p_rc_value']; ?></td>
	                        <td><?php echo $row['field_field_p_op_p_ic_value']; ?></td>
	                        <td><?php echo $row['ref_optic']; ?></td>
	                        <?php } ?>
	                        <!--optic_photometry -->

	                        <!--wise photometry -->
	                        <?php if($row['field_field_p_ir_w1_wise_value']){ ?> 
	                        <td><?php echo $row['field_field_p_ir_w1_wise_value']; ?></td>
	                        <td><?php echo $row['field_field_p_ir_w2_wise_value']; ?></td>
	                        <td><?php echo $row['field_field_p_ir_w3_wise_value']; ?></td>
	                        <td><?php echo $row['field_field_p_ir_w4_wise_value']; ?></td>
	                        <td><?php echo $row['field_field_p_ir_p_w1_wise_value']; ?></td>
	                        <td><?php echo $row['field_field_p_ir_p_w2_wise_value']; ?></td>
	                        <td><?php echo $row['field_field_p_ir_p_w3_wise_value']; ?></td>
	                        <td><?php echo $row['field_field_p_ir_p_w4_wise_value']; ?></td>
	                        <td><?php echo $row['field_field_p_ir_flag_2_value']; ?></td>
	                        <td><?php echo $row['ref_wise']; ?></td>
	                        <?php } ?>
	                        <!--wise photometry -->

	                         <!--radial velocity -->
	                        <?php if($row['field_field_s_rad_v_vr_value']){ ?> 
	                        <td><?php echo $row['field_field_s_rad_v_vr_value']; ?></td>
	                        <td><?php echo  $row['field_field_s_rad_p_vr_value']; ?></td>
	                        <td><?php echo  $row['ref_velocity']; ?></td>
	                        <?php } ?>
	                        <!--radial velocity -->

	                        <!--rotat velocity -->
	                        <?php if($row['field_s_rot_vsin_value']){ ?> 
	                        <td><?php echo $row['field_s_rot_vsin_value']; ?></td>
	                        <td><?php echo  $row['field_s_rot_p_vsin_value']; ?></td>
	                        <td><?php echo  $row['ref_rotat']; ?></td>
	                        <?php } ?>
	                        <!--rotat velocity -->

	                         <!--spectral type -->
	                        <?php if($row['field_s_spec_p_sp_value']){?> 
	                        <td><?php echo $row['field_s_spec_sp_select_value']; ?></td>
	                        <td><?php echo $row['field_s_spec_p_sp_value']; ?></td>
	                        <td><?php echo $row['ref_spec']; ?></td>
	                        <?php } ?>
	                        <!--spectral type -->

	                         <!--temperature type -->
	                        <?php if($row['field_s_temp_teff_value']){?> 
	                        <td><?php echo $row['field_s_temp_teff_value']; ?></td>
	                        <td><?php echo $row['field_s_temp_p_teff_value']; ?></td>
	                        <td><?php echo $row['ref_temperature']; ?></td>
	                        <?php } ?>
	                        <!--temperature type -->

	                        <!--Li,Hα -->
	                        <?php if($row['field_s_lih_ew_value']){?> 
	                        <td><?php echo $row['field_s_lih_ew_value']; ?></td>
	                        <td><?php echo  $row['field_s_lih_pew_value']; ?></td>
	                        <td><?php echo $row['field_s_lih_ewha_value']; ?></td>
	                        <td><?php echo $row['field_s_lih_pewha_value']; ?></td>
	                        <td><?php echo $row['ref_li_ha']; ?></td>
	                        <?php } ?>
	                        <!--Li,Hα -->

	                        <!--yso -->
	                        <?php if($row['field_field_exyso_class_value']){?> 
	                        <td><?php echo $row['field_field_exyso_class_value']; ?></td>
	                        <td><?php echo $row['field_field_ex_yso_pms_status_value']; ?></td>
	                        <td><?php echo $row['ref_yso']; ?></td>
	                        <?php } ?>
	                        <!--yso -->

	                       </tr>
	                       <?php } ?>
	                    </tbody>
	                    <tfoot>
	                    	<tr>
	                    		<?php    if($_FILES) { ?>
		                      	<th>Termos(s)</th>
		                      	<?php } ?>
		                      	<?php if($_POST['check_star_name'][0]){ ?>
	 							<th>STAR NAME</th>
	 							<?php } ?>
		                        <?php if($_POST['check_main'][0]){ ?>
		                        <th>MAIN IDENTIFIER</th>
		                        <?php } ?>
		                        <?php if($_POST['check_others'][0]){ ?>
		                        <th>OTHERS IDENTIFIERS</th>
		                        <?php } ?>

		                        <!--position -->
		                        <?php if($linha['field_pos_a_icrs_value']){ ?>
		                        <th>α (ICRS)</th>
		                        <th>δ (ICRS)</th>
		                        <th>σα</th>
		                        <th>σδ</th>
		                        <th>I (ICRS)</th>
		                        <th>b (ICRS)</th>
		                        <th>σI</th>
		                        <th>σb</th>
		                        <th>Position Reference</th>
		                        <?php } ?>
		                        <?php if($linha['field_pos_epra_value']){ ?>
		                        <th>Epoch (α or I)</th>
		                        <th>Epoch (δ or I)</th>
		                        <?php } ?>
		                        <!--position -->

		                        <!--proper motion-->
		                        <?php if($linha['field_pm_mi_alpha_value']){ ?>
		                        <th>μαcosδ</th>
		                        <th>μδ</th>
		                        <th>σμαcosδ </th>
		                        <th>σμδ</th>
		                        <th>Number of Points </th>
		                        <th>Proper Motion Reference </th>
		                        <?php } ?>
		                        <!--proper motion -->
		                        <!--parallax-->
		                        <?php if($linha['field_px_pi_value']){ ?>
		                        <th>π</th>
		                        <th>σπ</th>
		                        <th>Parallax Type </th>
		                        <th>Parallax Reference </th>
		                        <?php } ?>
		                        <!--parallax -->
		                        <!--ir photometry -->
		                        <?php if($linha['field_field_p_ir_j2mass_value']){ ?>
		                        <th>J</th>
		                        <th>H</th>
		                        <th>K</th>
		                        <th>σJ</th>
		                        <th>σH</th>
		                        <th>σK</th>
		                        <th>J,H,K Reference</th>
		                        <?php } ?>
		                        <!--ir photometry -->

		                        <!--gaia photometry -->
		                        <?php  if($linha['field_gaia_g_value']){ ?>
		                        <th>G</th>
		                        <th>BP</th>
		                        <th>RP</th>
		                        <th>σG</th>
		                        <th>σBP</th>
		                        <th>σRP</th>
		                        <th>Gaia Reference</th>
		                        <?php } ?>
		                        <!--gaia photometry -->

		                        <!--OPTIC -->
		                        <?php  if($linha['field_field_p_op_u_value']){ ?>
		                        <th>U</th>
		                        <th>B</th>
		                        <th>V</th>
		                        <th>Rc</th>
		                        <th>Ic</th>
		                        <th>σU</th>
		                        <th>σB</th>
		                        <th>σV</th>
		                        <th>σRc</th>
		                        <th>σIc</th>
		                        <th>Optic Reference</th>
		                        <?php } ?>
		                        <!--OPTIC -->

		                        <!--wise photometry-->
		                        <?php  if($linha['field_field_p_ir_w1_wise_value']){ ?>
		                        <th>W1(3.4µm)</th>
		                        <th>W2(4.6µm)</th>
		                        <th>W3(12µm)</th>
		                        <th>W4(22µm)</th>
		                        <th>σW1</th>
		                        <th>σW2</th>
		                        <th>σW3</th>
		                        <th>σW4</th>
		                        <th>FLAG</th>
		                        <th>Wise Reference</th>
		                        <?php } ?>
		                        <!--wise photometry -->

		                        <!--radial velocity-->
		                        <?php  if($linha['field_field_s_rad_v_vr_value']){ ?>
		                        <th>Vr</th>
		                        <th>σVr</th>
		                        <th>Radial Reference</th>
		                        <?php } ?>
		                        <!--radial velocity -->

		                        <!--rotat velocity-->
		                        <?php  if($linha['field_s_rot_vsin_value']){ ?>
		                        <th>Vsin(i)</th>
		                        <th>σVsin(i)</th>
		                        <th>Rotat Velocity</th>
		                        <?php } ?>
		                        <!--rotat velocity -->

		                        <!--spactral type-->
		                        <?php  if($linha['field_s_spec_p_sp_value']){ ?>
		                        <th>Spetral Type</th>
		                        <th>Uncertainty</th>
		                        <th>Spectral Reference</th>
		                        <?php } ?>
		                        <!--spactral type -->

		                        <!--temperature-->
		                        <?php  if($linha['field_s_temp_p_teff_value']){ ?>
		                        <th>Teff</th>
		                        <th>σTeff</th>
		                        <th>Temperature Reference</th>
		                        <?php } ?>
		                        <!--temperature -->

		                        <!--Li,Hα -->
		                        <?php  if($linha['field_s_lih_ew_value']){ ?>
		                        <th>EW(Li)</th>
		                        <th>σEW(Li)</th>
		                        <th>EW(Hα)</th>
		                        <th>σEW(Hα)</th>
		                        <th>Li,Ha Reference</th>
		                        <?php } ?>
		                        <!--Li,Hα -->
		                        <!--yso-->
		                        <?php  if($linha['field_field_exyso_class_value']){ ?>
		                        <th>SED Class</th>
		                        <th>Object Type</th>
		                        <th>YSO Refrence</th>
		                        <?php } ?>
		                        <!--yso -->
	                    	</tr>
	                    </tfoot>
	                    
	                </table>
        </div>

        <script>
	      $(function () {
	        $("#example1").DataTable();
	        $('#example2').DataTable({
	          "paging": true,
	          "lengthChange": false,
	          "searching": false,
	          "ordering": true,
	          "info": true,
	          "autoWidth": false
	        });
	      });
	    </script>
	</body>
</html>