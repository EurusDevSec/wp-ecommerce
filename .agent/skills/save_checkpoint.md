---
name: save_checkpoint
description: Summarize the current session and update the active memory file
---

# Skill Instructions
When the user invokes this skill, you must perform the following actions:
1. Review all modified files in the codebase during this session.
2. Update the `.agent/workflows/session_memory.md` file using your file-writing tool.
3. Keep the summary under 300 words, capturing the Active Task, recent Code Changes, and concrete Next Steps.
4. Update the checkboxes (`- [ ]` to `- [x]`) in `.agent/rules/PLAN.md` if any major feature was completed.
