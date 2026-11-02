# HostPinnacle cPanel Git Version Control — Frontend deployment

The main development branch contains React/TypeScript source and lacks a checked-in .cpanel.yml at its root. It is not directly deployable in cPanel Git Version Control.

A GitHub Actions build (workflow at .github/workflows/cpanel-website-package.yml) runs on main when the frontend changes. It produces the separate branch cpanel-frontend with a complete prebuilt React site and its own root-level .cpanel.yml.

The deployment file is:

    ---
    deployment:
      tasks:
        - /bin/mkdir -p $HOME/public_html
        - /bin/cp -R website/. $HOME/public_html/

This copies only compiled frontend assets from website into the primary domain document root. There is no Node, Composer or SSH requirement on cPanel for this static deployment.

## Steps in cPanel

1. Back up existing website files under public_html before overwriting them.
2. In GitHub Actions, confirm Carfolk — Package Built Website for cPanel succeeded and the branch cpanel-frontend exists.
3. In cPanel > Git Version Control > Manage > Basic Information, change Checked-Out Branch from main to cpanel-frontend, then Update.
4. In Pull or Deploy, click Update from Remote, then Deploy HEAD Commit.
5. Confirm File Manager shows public_html/index.html and public_html/assets directly.
6. Verify the domain uses public_html as its document root and has valid HTTPS, then open https://carfolkltd.com.

The branch needs to be checked out cleanly; never edit the cPanel managed repository directly. If cPanel reports a dirty working tree, preserve those changes outside the clone and create a fresh clean repository clone when necessary. Do not delete unreviewed user files. If the branch is not listed, refresh cPanel or clone again after the package branch has been created.

## Scope and limitations

The deployment branch deliberately contains only the frontend. The full Carfolk app depends on Laravel/PostgreSQL at /api/v1 and /manage. Vehicle inventory, enquiries and admin will not work until that backend is hosted. The frontend already shows honest disconnected states. This is not a complete dealership production launch.

Search indexing remains disabled (noindex and disallow robots) pending client-authorized launch. No demo data or backend secrets are published. Future package builds automatically update the GitHub deployment branch; cPanel pull/deploy is manual until the later SSH automation phase.
