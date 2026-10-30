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

2. Create the annotated tag:

   ```shell
   scripts/create-tag.sh
   ```

   The script shows the next version and the release notes, and asks for confirmation. If you answer anything other
   than `y`, it aborts without creating a tag. After the tag is created, it is shown with `git show --no-patch`.

   The version is derived from the commit types: a `feat` results in a minor version, a `fix` in a patch version, and a
   breaking change (`!` after the type or a `BREAKING CHANGE:` footer) in a major version.

3. Check the tag shown by the script, then push it:

   ```shell
   git push origin <tag>
   ```

   If the tag is wrong, delete it with `git tag -d <tag>` and run the script again.

## Commit messages

Use [Conventional Commits][conventional-commits] (`feat:`, `fix:`, `chore:`, `test:`, `ci:`, ...), so the changes are
grouped correctly in the release notes. Commits that do not follow the convention are listed under "Other" and do not
influence the version number.

[conventional-commits]: https://www.conventionalcommits.org
[git-cliff]: https://git-cliff.org
