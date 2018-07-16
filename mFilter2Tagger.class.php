<?php
class TaggerFilter extends mse2FiltersHandler {
	public function getTaggerValues(array $groupids, array $ids) {
		$filters = array();
		$q = $this->modx->newQuery('TaggerTagResource');
		$q->innerJoin('TaggerTag', 'TaggerTag', '`TaggerTag`.`id` = `TaggerTagResource`.`tag` AND `TaggerTag`.`group` IN ("' . implode('","', $groupids).'")');
		$q->select('TaggerTagResource.resource, TaggerTag.id, TaggerTag.group');
		$q->where(array('TaggerTagResource.resource:IN' => $ids));
		$tstart = microtime(true);
		if ($q->prepare() && $q->stmt->execute()) {
			$this->modx->queryTime += microtime(true) - $tstart;
			$this->modx->executedQueries++;
			while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
				foreach ($row as $k => $v) {
					$v = trim($v);
					if ($v == '' || $k == 'resource' || $k == 'group') {continue;}
					if (isset($filters[$row['group']][$v])) {
						$filters[$row['group']][$v][] = $row['resource'];
					}
					else {
						$filters[$row['group']][$v] = array($row['resource']);
					}
				}
			}
		}
		return $filters;
	}
	function buildTaggerFilter(array $values) {
        $tagIDs = array_keys($values);
        $results = array();
        $q = $this->modx->newQuery('TaggerTag', array('id:IN' => $tagIDs));
        $q->select('id,tag,alias,group');
        if ($q->prepare() && $q->stmt->execute()) {
            	$names = array();
	    	$alias = array();
		$group = array();
        	while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                	$names[$row['id']] = $row['tag'];
	            	$alias[$row['id']] = $row['alias'];
			$group[$row['id']] = $row['group'];
            	}
	        foreach ($values as $tagID => $ids) {
	                $title = !isset($names[$tagID]) ? $this->modx->lexicon('mse2_filter_boolean_no') : $names[$tagID];
	                $results[$title] = array(
		                'title' => $title,
		                'value' => $tagID,
		                'resources' => $ids
	                );
	    	}
        }
	ksort($results);
        return $results;
    }
}
