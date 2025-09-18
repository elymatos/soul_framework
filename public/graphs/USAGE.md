# Chapter Graph Viewer - Usage Guide

## Quick Start

1. **Open the Viewer**: Navigate to `/public/graphs/chapter_graph_viewer.html` in your browser
2. **Select a Chapter**: Use the dropdown to choose from 40+ available chapters
3. **Load Graph**: Click "Load Chapter" button
4. **Explore**: Graph appears in main area, statistics and legend in sidebar

## Features

### Chapter Selection
- **40+ Available Chapters** from the Formal Theory of Commonsense Psychology
- **Automatic filename mapping** - no need to know exact filenames
- **Error handling** for missing chapters

### Graph Visualization
- **Interactive D3.js visualization** with force-directed layout
- **Draggable nodes** for manual positioning
- **Node labels** showing names (can be toggled on/off)
- **Hover tooltips** showing detailed node information positioned near nodes
- **Color-coded nodes** by type and importance

### Sidebar Features
- **Statistics Dashboard**: Real-time stats displayed in compact grid
  - Axiom count, Predicate count, Variable count, Connection count
- **Top Predicates**: Most frequent predicates with usage counts
- **Color Legend**: Visual guide for all node types and connection types
- **Compact Layout**: All information accessible without scrolling

### Filtering Controls
- **Node Type Filter**: Show only axioms, predicates, or variables
- **Complexity Filter**: Filter axioms by complexity (simple/moderate/complex)
- **Show Labels Toggle**: Show/hide node labels for better performance
- **Reset View**: Restart force simulation
- **Center Graph**: Recenter the visualization

### Zoom and Pan Controls
- **Mouse Wheel**: Zoom in/out over the graph
- **Click + Drag**: Pan around the graph
- **Zoom In/Out Buttons**: Precise zoom control
- **Reset Zoom**: Return to fit-to-screen view
- **Drag Nodes**: Move individual nodes (temporarily disables panning)

## Node Types & Colors

| Type | Color | Description |
|------|-------|-------------|
| Simple Axioms | Blue | Low complexity axioms |
| Moderate Axioms | Orange | Medium complexity axioms |
| Complex Axioms | Red | High complexity axioms |
| High-freq Predicates | Green | Frequently used predicates |
| Medium-freq Predicates | Purple | Moderately used predicates |
| Low-freq Predicates | Brown | Rarely used predicates |
| Variables | Pink | Frequently used variables |

## Link Types

- **Gray lines**: Axiom uses predicate
- **Light gray lines**: Axiom has variable
- **Very light lines**: Predicates co-occur

## Recommended Chapters for Analysis

### Start with Simple Chapters:
- **Chapter 11**: Defeasibility (14 axioms, 33 nodes) - Clear patterns
- **Chapter 19**: Persons (14 axioms, 48 nodes) - Cognitive concepts
- **Chapter 34**: Causes of Failure (6 axioms, 27 nodes) - Minimal example

### Intermediate Complexity:
- **Chapter 23**: Memory (38 axioms, 116 nodes) - Cognitive processes
- **Chapter 31**: Plans (53 axioms, 170 nodes) - Planning concepts

### Advanced Analysis:
- **Chapter 28**: Goals (82 axioms, 207 nodes) - Complex goal structures
- **Chapter 49**: Emotions (120 axioms, 290 nodes) - Largest chapter
- **Chapter 45**: Execution Control (48 axioms, 159 nodes) - Complex processes

## Layout Features

### Full-Screen Design
- **No scrollbars**: Graph area contained within viewport
- **Responsive sidebar**: 300px fixed width, scrollable content
- **Flexible main area**: Graph takes remaining space
- **Header/controls**: Fixed at top, always accessible

### Optimized for Large Graphs
- **Zoom and pan**: Navigate large networks without scrolling page
- **Sidebar efficiency**: All metadata in compact, organized layout
- **Performance**: Smooth interaction with 200+ nodes

## Technical Details

### File Structure
- **JSON files**: Located in `/public/graphs/`
- **Naming pattern**: `chapter_X_chapter_XX_[topic]_graph.json`
- **Size range**: 22KB to 215KB per file

### Browser Requirements
- **Modern browser** with D3.js support
- **Local web server** (graphs won't load from file:// protocol)
- **JavaScript enabled**
- **Viewport**: Minimum 1024px width recommended

## Troubleshooting

**Graph not loading?**
- Check browser console for errors
- Verify web server is running
- Ensure JSON files are in `/public/graphs/`

**Poor performance?**
- Try smaller chapters first (11, 19, 34)
- Use filters to reduce visible nodes
- Reset view if simulation gets stuck

**Missing chapters?**
- Only 40 of 45 chapters have valid data
- Some chapters had parsing issues in original data

This viewer provides an efficient way to explore the SOUL Framework's conceptual structure chapter by chapter, making the complex theory more accessible and understandable.