Project: "Context Generator"
Context:
- src/McpServer/Action/Tools: directory with mcp tools
  - src/McpServer/Action/Tools/ProblemSolver/Analyze: first step tools
  - src/McpServer/Action/Tools/ProblemSolver/Barnstorming: second step tools
  - src/McpServer/Action/Tools/ProblemSolver/Plan: 3 step tools
  - src/McpServer/Action/Tools/ProblemSolver/Changes: 4 step tools
  - tool example: src/McpServer/Action/Tools/Filesystem/FileReadAction.php
- src/McpServer/ProblemSolver: service directory
  - src/McpServer/ProblemSolver/Entity
  - src/McpServer/ProblemSolver/Services
  - src/McpServer/ProblemSolver/Repository
-



# Разработка системы управления задачами с возможностью паузы и продолжения



## 1 Step: GET AND Analyze a problem
- Save the original problem
- First Analyze the problem
- clarify all the issues of the problem
- Detect problem type:
    - feature (it is necessary to add new or change functionality or business logic)
    - bug (fix a bug in a program or system that causes the program to behave unexpectedly)
    - research (explore the possibility of implementing new features or technologies)
    - refactoring (code redesign, code rework, equivalent transformation of algorithms - the process of changing the internal structure of a program without affecting its external behavior and aimed at making it easier to understand how it works.)
- detect default project
- approve the problem assignment
- create draft of the brainstorming guide based on the problem description
- actions
  - add problem
    - request
      - problem_id - if exists
      - original_problem
    - return
      - First Analyze instructions
  - save brainstorming draft
    - request
      - problem_id
      - problem_type
      - default_project
      - brainstorming_draft
      - problem_context(see @instructions/save context)
    - return
      - pause instruction
  - continue or restore step (for restore step need reason) return  (see @INSTRUCTIONS/CONTINUE OR RESTORE)
  - request
    - problem_id
  - return
    - problem
    - brainstorming_draft
    - problem_context_formated
    - brainstorm instructions
    - for restore: restore reason with instruction

## 2 Step: Barnstorming problem
- continue or restore step (for restore step need reason) returns (see @INSTRUCTIONS/CONTINUE OR RESTORE)
- clarify the list of participants needed for the brainstorming session
  - **Project developers** - get project developers to understand the implementation details
  - **Business analyst** for understanding the needs and requirements clearly.
  - **Subject-matter expert** to check ideas are realistic and useful.
  - **AI prompt engineer** to ensure the instructions are understandable and useful for AI-generated ideas.

- Start multi-round Barnstorming
    - In each round, participants must complete the following steps:
        - Each participant should have several thoughts about the task in their head and think about them three times.
        - Each participant can get the information he needs about the project (source file or directory overview).
        - Each participant must present his/her thoughts to all participants.
        - Each participant must speak out about the ideas of other participants.
    - if any of the participants disagree with something or there is an unfinished conversation, then repeat the round, but no more than 2 times in a row
    - Save the discussion in the Barnstorming round
    - give me the floor, I can add something to the conversation, so the round will be repeated or the discussion will be concluded
- collect all the discussion
- create a task for each project affected by the issue
- each task need contains next info:
  - project name
  - developer info
  - short description
- actions
  - continue or restore step (for restore step need reason) returns (see @INSTRUCTIONS/CONTINUE OR RESTORE)
  - project developers
    - request
      - project_name
    - return
      - list developers with developer id
      - if wrong project name instruction to call write tool or fix project name
  - task list
    - request
      - problem_id
    - return
      - list task titles with task number
  - add or modify task
    - request
      - problem_id
      - task_number
      - project name
      - project_developer_id
      - title
      - description
      - context
    - return
      - continue instructions
  - delete task
    - request
      - problem_id
      - task_number
    - return
      - continue instructions
  - approve task
    - request
      - problem_id
      - task_number
    - return
      - continue instructions





## 3 Step: Task Plan
- by approved task
- create a task for each project affected by the issue
- task need have next details:
  - project name
  - developer info
  - short description
  - list changes:
    - file path
    - change type: new, change, delete
    - description - short information about updates
    - context:
      - directories - list of directory overview (modules components packages)
      - files - list of file sources needed to make changes
- actions
  - continue or restore step (for restore step need reason) returns (see @INSTRUCTIONS/CONTINUE OR RESTORE)
  - add or modify task change description
    - request
      - problem_id
      - task_number
      - filePath
      - change type: new, change, delete
      - goal
      - change description
      - context
    - return
      - continue instructions
  - remove task change description
    - request
      - problem_id
      - task_number
      - filePath
  - overview task changes
    - request
      - problem_id
      - task_number
    - return
      - task description
      - changes
        - filePath
        - change type
        - goal
      - instruction to approve if needed
  - approve task changes
    - request
      - problem_id
      - task_number
    - return
      - continue instruction
      - if all approved pause



## 4 Step: Solve Task
- continue
- a
- get next change with context and instructions
- make and changes
- actions
    - make_change
      - request
        - problem_id
        - task_number
        - filePath
        - content
      - return
        - continue instructions with next change or pause if complete




## INSTRUCTIONS

### CONTINUE OR RESTORE
- continue or restore step (for restore step need reason) return
- request
    - problem_id
- return
    - problem
    - brainstorming_draft
    - problem_context_formated
    - step instructions
    - for restore: restore reason with instruction

### SAVE CONTEXT
property context is object with properties
- directories - list of directory overview (modules components packages)
- files - list of file sources needed to make changes
- packages - list of vendor packages
- documents - some content in md format




