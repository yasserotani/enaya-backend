---
applyTo: '**'
---

Never use git add, git commit, or git push unless the user explicitly asks for it. If the user wants a patch, review the workspace changes and explain them without staging or committing. Do not create commits, tags, or remote updates as part of regular work.

Follow project conventions and existing code patterns before introducing new patterns. Keep changes precise and limited to the task. Do not modify unrelated files or fix unrelated issues. Prefer surgical edits and clear, minimal diffs.

When a requirement is ambiguous, ask a focused clarifying question before making assumptions. Validate changes with the project's existing commands and tests when available. Preserve user intent and avoid unnecessary automation.

Be careful with repository history. Treat the working tree as read/write only for the current task unless the user explicitly requests a commit or a push.
