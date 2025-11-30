<?php
/**
 * Scholarship Tree Model
 * Organizes scholarships in a hierarchical structure
 */

/**
 * Tree node for scholarship organization.
 */
class TreeNode {
    public $value;
    public $children = [];

    /**
     * Constructor for TreeNode class, initializes the node with a value.
     */
    public function __construct($value) {
        $this->value = $value;
    }

    /**
     * Adds a child node to this tree node.
     */
    public function addChild($key, $node) {
        $this->children[$key] = $node;
    }
}

/**
 * Builds scholarship tree organized by level, field, and type.
 */
class ScholarshipTree {
    private $root;

    /**
     * Constructor for ScholarshipTree, initializes the tree with a root node.
     */
    public function __construct() {
        $this->root = new TreeNode('root');
    }

    /**
     * Adds a scholarship to the tree structure, organizing it by level, field, and type.
     * Creates necessary intermediate nodes if they don't exist.
     */
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

    /**
     * Retrieves scholarships filtered by education level and optionally by field.
     */
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

    /**
     * Gets all available education levels in the tree.
     */
    public function getLevels() {
        return array_keys($this->root->children);
    }

    /**
     * Gets all fields of study for a given education level.
     */
    public function getFieldsByLevel($level) {
        if (!isset($this->root->children[$level])) {
            return [];
        }

        return array_keys($this->root->children[$level]->children);
    }
}
?>