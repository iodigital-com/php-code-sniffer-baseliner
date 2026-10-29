# Releasing

Releases are tags on the `main` branch. The tag message contains the release notes, which are generated from the
[Conventional Commits][conventional-commits] since the previous tag by [git-cliff][git-cliff] (see `cliff.toml`).
When a tag is pushed, the `release` GitHub Actions workflow creates a GitHub release with the tag message as its
description. Packagist picks up the tag by itself.

## Creating a release

1. Merge `develop` into `main` and make sure your local `main` is up to date:

   ```shell
   git checkout main
   git pull
   ```

2. Preview the next version and the release notes:

   ```shell
   git cliff --bumped-version --unreleased --use-branch-tags
   git cliff --bump --unreleased --use-branch-tags
   ```

   The version is derived from the commit types: a `feat` results in a minor version, a `fix` in a patch version, and a
   breaking change (`!` after the type or a `BREAKING CHANGE:` footer) in a major version.

3. Create the annotated tag locally:

   ```shell
   git tag -a "$(git cliff --bumped-version --unreleased --use-branch-tags)" -m "$(git cliff --bump --unreleased --use-branch-tags)" --cleanup=verbatim
   ```

   `--cleanup=verbatim` is required, otherwise git removes the markdown headings (lines starting with `#`) from the
   tag message.

4. Check the tag, then push it:

   ```shell
   git show --no-patch <tag>
   git push origin <tag>
   ```

## Commit messages

Use [Conventional Commits][conventional-commits] (`feat:`, `fix:`, `chore:`, `test:`, `ci:`, ...), so the changes are
grouped correctly in the release notes. Commits that do not follow the convention are listed under "Other" and do not
influence the version number.

[conventional-commits]: https://www.conventionalcommits.org
[git-cliff]: https://git-cliff.org
