# Cursor Rules for Sandwich Factory Demo

This directory contains the Cursor rules for the Sandwich Factory demo application, organized into logical files for better maintainability.

## Rule Files

- **project-overview.md** - Project description, tech stack, and purpose
- **architecture.md** - Backend and frontend architecture guidelines
- **coding-standards.md** - PHP/Laravel and TypeScript/Vue coding standards
- **domain-model.md** - Business domain entities and workflow steps
- **temporal-integration.md** - Temporal.io workflow and activity guidelines
- **realtime-features.md** - Real-time updates and dashboard components
- **ui-ux-guidelines.md** - Design system and user experience standards
- **development-setup.md** - Environment setup and development tools
- **comparison-implementation.md** - Guidelines for comparing traditional Laravel vs Temporal approaches

## Usage

These rules are automatically loaded by Cursor when working in this project. They provide context and guidelines for:

- Code generation and suggestions
- Architecture decisions
- Coding standards enforcement
- Best practices implementation
- Comparison implementation between traditional and Temporal approaches

## Maintenance

When updating rules:
1. Edit the appropriate file based on the topic
2. Keep rules focused and specific to their domain
3. Update this README if adding new rule files
4. Ensure rules are consistent across files

## Key Features

This project demonstrates the advantages of Temporal.io by implementing the same sandwich factory workflow using both traditional Laravel queued jobs and Temporal workflows, allowing for direct comparison of:
- Implementation complexity
- Error handling capabilities
- Observability and debugging
- State management approaches
- Recovery mechanisms
