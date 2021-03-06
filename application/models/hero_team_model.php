<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Hero_Team_model extends Base_model {

	private $table = NULL;
	public function __construct() 
	{
		parent::__construct();
		$this->load->model('hero_model');
		$this->table=parent::init('hero_team');
	}
	
	public function select_all($params)
	{
		$sql = "select *  from `{$this->table}` order by `id`";
		$query = $this->db->query($sql);
		$hero_team = $query->result();
		$pagenum = $params['pagenum'];
		$pagesize = $params['pagesize'];
		$begin = ($pagenum-1)*$pagesize;
		$hero_team = array_slice($hero_team,$begin,$pagesize);
		return $hero_team;
	}
		public function is_update($hero_team_timestamp)
	{
		$sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE UNIX_TIMESTAMP(`create_time`)>$hero_team_timestamp";
		$query = $this->db->query($sql);
		$hero_team_count = $query->row_array();
		return $hero_team_count['count'];
	}
		public function save($params)
		{
		if ( !$params OR ! is_array($params) ) {
			return FALSE;
		}
		
		if(isset($params['id']))
		{
			$id = intval($params['id']);
		}else{
			$id=0;
		}
		
		$sql = "SELECT * FROM `{$this->table}` WHERE `id` = ?";
		$query = $this->db->query( $sql, array($id));
		if ($query->num_rows()) {
			$result = $query->row_array();
			$id = $result['id'];
		}else{
			$id=0;
		}
		$sql = ($id > 0 ? 'UPDATE ' : 'INSERT INTO ')." `{$this->table}` SET ";
		$db_values = array();
		foreach ($params as $db_field => $db_value)
		{
			$db_field = trim(strip_tags($db_field));
			if ( ! $db_field) {
				continue;
			}	
			$sql .= "`{$db_field}` = ?, ";
			$db_values[] = is_numeric($db_value) ? $db_value : addslashes($db_value);
		}
		$sql = trim( rtrim( trim($sql) , ',') );
		
		if ($id > 0) {
			$sql .= ' WHERE `id` = ? ';
			$db_values[] = $id;
		}

		$this->db->query($sql, $db_values);
		return $id ? $this->db->affected_rows() : $this->db->insert_id();
	}
	
	public function get_zudui($params)
	{
		$params_count = count($params);
		if($params_count==5)
		{
			$params = json_encode($params);
			return $params;
		}else{
			$result_tt = file_get_contents(FCPATH.'/data/zudui_tt.inc');
			$result_yz = file_get_contents(FCPATH.'/data/zudui_yz.inc');
			$hero_team=file_get_contents(FCPATH.'/data/hero_team.inc');
			$hero = file_get_contents(FCPATH.'/data/hero.inc');
			$result_tt = json_decode($result_tt,true);
			$result_yz = json_decode($result_yz,true);
			$hero_team = json_decode($hero_team,true);
			$hero = json_decode($hero,true);
			$zudui_result = array();

			$zudui_result_tt = $this->make_zudui($params,$result_tt,$hero_team,$hero);
			$zudui_result_yz = $this->make_zudui($params,$result_yz,$hero_team,$hero);
			$zudui_result['tt_hero_id'] =$zudui_result_tt;
			$zudui_result['yz_hero_id'] =$zudui_result_yz;
			$zudui_result = json_encode($zudui_result);
			return $zudui_result;
		}
	}
	public function make_zudui($params,$result,$hero_team,$hero)
	{
			$rs = array();
			foreach($params as $k =>$v)
			{
				if(isset($result[$v]))
				{
					$rs[$k] = $result[$v];
				}
			}
			$rs2 = explode(',',implode(',',$rs));
			$rs2=array_count_values($rs2); 
			$rs3 = $rs2;
			asort($rs2); 
			sort($rs3);
			$id = $rs3[count($rs3)-1];
			$ids = array_search($id,$rs2,true);
			$zudui = explode(',',$hero_team[$ids]['hero_id']);
			$intersection = array_intersect($params,$zudui);
			$params_diff = array_diff($params,$intersection);
			$hero_team_diff = array_diff($zudui,$intersection);

			$zudui_result = $intersection;
			foreach($hero_team_diff as $k =>$v)
			{
					foreach($params_diff as $k1 =>$q)
					{
						
						if(isset($hero[$q]))
						{
							if($hero[$q]['type']==$hero[$v]['type'])
							{
								$max = 0;
								
								if($hero[$q]['sort']>$max)
								{
									$max = $q;
									
								}
								$max = intval($max);
							}else{
								$max = 0;
								
								if($hero[$q]['sort']>$max)
								{
									$max = $q;
									
								}
								
							
							}
						}
							
					}
					
					if(isset($max) && $max)
					{
						unset($hero[$max]);
						array_push($zudui_result,$max);
					}
			}
				sort($zudui_result);
				return $zudui_result;
	}
}
