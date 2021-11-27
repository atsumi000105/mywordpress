let
  rev = "46725ae611741dd6d9a43c7e79d5d98ca9ce4328";
  nixpkgs = fetchTarball {
    url = "https://github.com/NixOS/nixpkgs/archive/${rev}.tar.gz";
    sha256 = "11srp3zfac0ahb1mxzkw3czlpmxc1ls7y219ph1r4wx2ndany9s9";
  };
  pkgs = import nixpkgs {};
  default = import ./default.nix;
in with pkgs;
mkShell {
  buildInputs = default;

  WORDPRESS_PATH = lib.findFirst (x: (builtins.hasAttr "pname" x) && "wordpress" == x.pname) "" default;
}
