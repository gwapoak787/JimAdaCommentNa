<?php
// Quick Sort implementation for scholarships

class ScholarshipSorter {
    
    // Quick Sort by deadline (ascending - earliest first)
    public static function sortByDeadline(&$scholarships, $low = 0, $high = null) {
        if ($high === null) {
            $high = count($scholarships) - 1;
        }
        
        if ($low < $high) {
            $pi = self::partitionDeadline($scholarships, $low, $high);
            self::sortByDeadline($scholarships, $low, $pi - 1);
            self::sortByDeadline($scholarships, $pi + 1, $high);
        }
        
        return $scholarships;
    }
    
    // Partition helper for deadline sort
    private static function partitionDeadline(&$arr, $low, $high) {
        $pivot = strtotime($arr[$high]['deadline']);
        $i = $low - 1;
        
        for ($j = $low; $j < $high; $j++) {
            if (strtotime($arr[$j]['deadline']) < $pivot) {
                $i++;
                // Swap
                $temp = $arr[$i];
                $arr[$i] = $arr[$j];
                $arr[$j] = $temp;
            }
        }
        
        // Swap pivot
        $temp = $arr[$i + 1];
        $arr[$i + 1] = $arr[$high];
        $arr[$high] = $temp;
        
        return $i + 1;
    }
    
    // Quick Sort by amount (descending - highest first)
    public static function sortByAmount(&$scholarships, $low = 0, $high = null) {
        if ($high === null) {
            $high = count($scholarships) - 1;
        }
        
        if ($low < $high) {
            $pi = self::partitionAmount($scholarships, $low, $high);
            self::sortByAmount($scholarships, $low, $pi - 1);
            self::sortByAmount($scholarships, $pi + 1, $high);
        }
        
        return $scholarships;
    }
    
    // Partition helper for amount sort (descending)
    private static function partitionAmount(&$arr, $low, $high) {
        $pivot = $arr[$high]['amount'];
        $i = $low - 1;
        
        for ($j = $low; $j < $high; $j++) {
            if ($arr[$j]['amount'] > $pivot) {
                $i++;
                // Swap
                $temp = $arr[$i];
                $arr[$i] = $arr[$j];
                $arr[$j] = $temp;
            }
        }
        
        // Swap pivot
        $temp = $arr[$i + 1];
        $arr[$i + 1] = $arr[$high];
        $arr[$high] = $temp;
        
        return $i + 1;
    }
}

// Tree node for scholarship organization
class TreeNode {
    public $value;
    public $children = [];
    
    public function __construct($value) {
        $this->value = $value;
    }
    
    public function addChild($key, $node) {
        $this->children[$key] = $node;
    }
}

// Build scholarship tree (Level -> Field -> Type)
class ScholarshipTree {
    private $root;
    
    public function __construct() {
        $this->root = new TreeNode('root');
    }
    
    // Add scholarship to tree
    public function addScholarship($scholarship) {
        $level = $scholarship['education_level'];
        $field = $scholarship['field'];
        $type = $scholarship['scholarship_type'];
        
        // Create level node if not exists
        if (!isset($this->root->children[$level])) {
            $this->root->addChild($level, new TreeNode($level));
        }
        
        // Create field node if not exists
        if (!isset($this->root->children[$level]->children[$field])) {
            $this->root->children[$level]->addChild($field, new TreeNode($field));
        }
        
        // Create type node if not exists
        if (!isset($this->root->children[$level]->children[$field]->children[$type])) {
            $this->root->children[$level]->children[$field]->addChild($type, []);
        }
        
        // Add scholarship to type node
        if (!is_array($this->root->children[$level]->children[$field]->children[$type])) {
            $this->root->children[$level]->children[$field]->children[$type] = [];
        }
        
        $this->root->children[$level]->children[$field]->children[$type][] = $scholarship;
    }
    
    // Get scholarships by level and field
    public function getByLevelAndField($level, $field = null) {
        if (!isset($this->root->children[$level])) {
            return [];
        }
        
        $result = [];
        $levelNode = $this->root->children[$level];
        
        if ($field === null) {
            // Return all scholarships for this level
            foreach ($levelNode->children as $fieldNode) {
                foreach ($fieldNode->children as $typeNode) {
                    if (is_array($typeNode)) {
                        $result = array_merge($result, $typeNode);
                    }
                }
            }
        } else {
            // Return scholarships for specific level and field
            if (isset($levelNode->children[$field])) {
                $fieldNode = $levelNode->children[$field];
                foreach ($fieldNode->children as $typeNode) {
                    if (is_array($typeNode)) {
                        $result = array_merge($result, $typeNode);
                    }
                }
            }
        }
        
        return $result;
    }
    
    // Get all levels
    public function getLevels() {
        return array_keys($this->root->children);
    }
    
    // Get fields for a level
    public function getFieldsByLevel($level) {
        if (!isset($this->root->children[$level])) {
            return [];
        }
        
        return array_keys($this->root->children[$level]->children);
    }
}

?>
