# Technical Notes

## The Biggest Docker Problem I Faced

After my first successful build, Docker reported a warning: `JSONArgsRecommended`. It indicated that my `CMD` instruction was written in *shell form*. In this form, the application runs as a child of a shell process, so the container may not receive OS signals such as `SIGTERM` correctly. This can cause slow or unclean shutdowns when running `docker stop`.

**Solution:** I rewrote the instruction in *exec (JSON) form*. Because I needed to run the database migration before starting the server, I wrapped both commands in `sh -c`:

```dockerfile
CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000"]
```

While fixing this, I also learned two important details about running Laravel in a container:

- The server must bind to `0.0.0.0` (not `127.0.0.1`), otherwise the application is unreachable from outside the container.
- SQLite stores data in a file inside the container, so tasks are lost when the container is removed. A Docker volume would solve this for long-term persistence.

## The Most Important Git/GitHub Lesson I Learned

My README screenshots appeared broken on GitHub. After investigating, I found that Windows was hiding file extensions, so my images were committed as `.png.png` and the README links pointed to files that did not exist. I diagnosed the problem with `git ls-files`, which shows exactly what Git tracks, and fixed it with `git mv` followed by a clear `fix:` commit.

The key lesson: **always verify what was actually committed, not just what appears on my local machine.** I also learned that small, meaningful commits with consistent prefixes (`docker:`, `docs:`, `fix:`, `chore:`) make the project history easy to follow and show real progress. Finally, I made sure sensitive and generated files such as `.env` and `vendor/` stay out of the repository through `.gitignore`.