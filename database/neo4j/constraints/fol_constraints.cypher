// ========================================
// FOL Axioms Neo4j Constraints and Indexes
// ========================================

// LogicalAxiom node constraints
CREATE CONSTRAINT logical_axiom_id_unique IF NOT EXISTS
FOR (axiom:LogicalAxiom) REQUIRE axiom.axiom_id IS UNIQUE;

CREATE CONSTRAINT logical_axiom_name_unique IF NOT EXISTS  
FOR (axiom:LogicalAxiom) REQUIRE axiom.name IS UNIQUE;

// PsychologicalPredicate node constraints
CREATE CONSTRAINT psychological_predicate_name_unique IF NOT EXISTS
FOR (pred:PsychologicalPredicate) REQUIRE pred.name IS UNIQUE;

// LogicalFrame node constraints  
CREATE CONSTRAINT logical_frame_name_unique IF NOT EXISTS
FOR (frame:LogicalFrame) REQUIRE frame.name IS UNIQUE;

// Performance indexes for LogicalAxiom
CREATE INDEX logical_axiom_pattern_index IF NOT EXISTS
FOR (axiom:LogicalAxiom) ON (axiom.pattern);

CREATE INDEX logical_axiom_complexity_index IF NOT EXISTS
FOR (axiom:LogicalAxiom) ON (axiom.complexity);

CREATE INDEX logical_axiom_domain_index IF NOT EXISTS
FOR (axiom:LogicalAxiom) ON (axiom.domain);

CREATE INDEX logical_axiom_defeasible_index IF NOT EXISTS
FOR (axiom:LogicalAxiom) ON (axiom.defeasible);

CREATE INDEX logical_axiom_confidence_index IF NOT EXISTS
FOR (axiom:LogicalAxiom) ON (axiom.confidence);

CREATE INDEX logical_axiom_chapter_index IF NOT EXISTS
FOR (axiom:LogicalAxiom) ON (axiom.chapter);

// Performance indexes for PsychologicalPredicate
CREATE INDEX predicate_type_index IF NOT EXISTS
FOR (pred:PsychologicalPredicate) ON (pred.predicate_type);

CREATE INDEX predicate_arity_index IF NOT EXISTS
FOR (pred:PsychologicalPredicate) ON (pred.arity);

CREATE INDEX predicate_domain_index IF NOT EXISTS
FOR (pred:PsychologicalPredicate) ON (pred.domain);

CREATE INDEX predicate_usage_index IF NOT EXISTS
FOR (pred:PsychologicalPredicate) ON (pred.usage_count);

CREATE INDEX predicate_activation_index IF NOT EXISTS
FOR (pred:PsychologicalPredicate) ON (pred.activation_strength);

// Performance indexes for LogicalFrame
CREATE INDEX logical_frame_type_index IF NOT EXISTS
FOR (frame:LogicalFrame) ON (frame.frame_type);

CREATE INDEX logical_frame_domain_index IF NOT EXISTS
FOR (frame:LogicalFrame) ON (frame.domain);

CREATE INDEX logical_frame_complexity_index IF NOT EXISTS
FOR (frame:LogicalFrame) ON (frame.complexity);

CREATE INDEX logical_frame_defeasible_index IF NOT EXISTS
FOR (frame:LogicalFrame) ON (frame.defeasible);

CREATE INDEX logical_frame_confidence_index IF NOT EXISTS
FOR (frame:LogicalFrame) ON (frame.confidence);

CREATE INDEX logical_frame_usage_index IF NOT EXISTS
FOR (frame:LogicalFrame) ON (frame.usage_count);

CREATE INDEX logical_frame_success_rate_index IF NOT EXISTS
FOR (frame:LogicalFrame) ON (frame.success_rate);

// Composite indexes for common query patterns
CREATE INDEX axiom_domain_complexity_index IF NOT EXISTS
FOR (axiom:LogicalAxiom) ON (axiom.domain, axiom.complexity);

CREATE INDEX predicate_type_domain_index IF NOT EXISTS
FOR (pred:PsychologicalPredicate) ON (pred.predicate_type, pred.domain);

CREATE INDEX frame_type_domain_index IF NOT EXISTS
FOR (frame:LogicalFrame) ON (frame.frame_type, frame.domain);

// Full-text search indexes
CREATE FULLTEXT INDEX axiom_content_fulltext IF NOT EXISTS
FOR (axiom:LogicalAxiom) ON EACH [axiom.english, axiom.description, axiom.title];

CREATE FULLTEXT INDEX predicate_content_fulltext IF NOT EXISTS
FOR (pred:PsychologicalPredicate) ON EACH [pred.name, pred.description];

CREATE FULLTEXT INDEX frame_content_fulltext IF NOT EXISTS
FOR (frame:LogicalFrame) ON EACH [frame.name, frame.description];

// Relationship indexes for activation propagation
CREATE INDEX axiom_predicate_relationship_index IF NOT EXISTS
FOR ()-[rel:INVOLVES_PREDICATE]-() ON (rel.strength);

CREATE INDEX predicate_activation_relationship_index IF NOT EXISTS
FOR ()-[rel:ACTIVATES]-() ON (rel.strength, rel.trigger_type);

CREATE INDEX frame_derivation_relationship_index IF NOT EXISTS
FOR ()-[rel:DERIVED_FROM]-() ON (rel.derivation_type);

CREATE INDEX agent_implementation_relationship_index IF NOT EXISTS
FOR ()-[rel:IMPLEMENTED_BY]-() ON (rel.priority);

// K-line learning indexes (extending existing)
CREATE INDEX kline_fol_context_index IF NOT EXISTS
FOR (kline:KLine) ON (kline.fol_context);

CREATE INDEX kline_axiom_pattern_index IF NOT EXISTS
FOR (kline:KLine) ON (kline.axiom_pattern);