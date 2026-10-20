/*
 * Form preview of the backend module "OAP Forms": the toolbar above the tree and the jumps of the
 * protocol. The tree itself works without this file - it is built of details elements.
 */

const tree = document.getElementById('oapfp-tree');

if (tree !== null) {
  const branches = () => tree.querySelectorAll('details');

  /*
   * In structure only the items and the attributes of a group are hidden, so a group without
   * nested groups has nothing left to show. Those nodes are folded and lose the caret and the
   * pointer - a leaf of the tree, not a branch that opens into nothing. A finding stays visible,
   * so a group that carries one keeps its caret.
   */
  const isEmptyInStructureOnly = (branch) => {
    if (! branch.classList.contains('oapfp-group')) {
      return false;
    }

    return branch.querySelector(':scope > .oapfp-group__body > .oapfp-children') === null
      && branch.querySelector(':scope > .oapfp-group__body > .oapfp-callout') === null;
  };

  const applyStructureOnly = () => {
    const active = tree.classList.contains('oapfp-tree--structure-only');
    branches().forEach((branch) => {
      if (! isEmptyInStructureOnly(branch)) {
        return;
      }
      if (active) {
        branch.dataset.oapfpOpen = branch.open ? '1' : '0';
        branch.open = false;
        branch.classList.add('oapfp-node--static');
        return;
      }
      branch.classList.remove('oapfp-node--static');
      if (branch.dataset.oapfpOpen !== undefined) {
        branch.open = branch.dataset.oapfpOpen === '1';
        delete branch.dataset.oapfpOpen;
      }
    });
  };

  const expandAll = document.getElementById('oapfp-expand-all');
  if (expandAll !== null) {
    expandAll.addEventListener('click', () => {
      branches().forEach((branch) => { branch.open = true; });
      applyStructureOnly();
    });
  }

  const collapseAll = document.getElementById('oapfp-collapse-all');
  if (collapseAll !== null) {
    collapseAll.addEventListener('click', () => {
      branches().forEach((branch) => { branch.open = false; });
      applyStructureOnly();
    });
  }

  const structureOnly = document.getElementById('oapfp-structure-only');
  if (structureOnly !== null) {
    structureOnly.addEventListener('change', (event) => {
      tree.classList.toggle('oapfp-tree--structure-only', event.target.checked);
      applyStructureOnly();
    });
  }

  /* a static node does not open, not even on a click on its summary */
  tree.addEventListener('click', (event) => {
    const summary = event.target.closest('summary');
    if (summary === null || event.target.closest('a') !== null) {
      return;
    }
    if (summary.parentElement.classList.contains('oapfp-node--static')) {
      event.preventDefault();
    }
  });

  /* a link into a folded branch opens everything above its target */
  const reveal = () => {
    const anchor = decodeURIComponent(window.location.hash.slice(1));
    if (anchor === '') {
      return;
    }
    const target = document.getElementById(anchor);
    if (target === null) {
      return;
    }
    let node = target;
    while (node !== null && node !== document.body) {
      if (node.tagName === 'DETAILS') {
        node.open = true;
      }
      node = node.parentElement;
    }
    target.scrollIntoView({ block: 'center' });
  };

  window.addEventListener('hashchange', reveal);
  document.querySelectorAll('a.oapfp-ref').forEach((link) => {
    link.addEventListener('click', () => { window.setTimeout(reveal, 0); });
  });

  /* the uid leads to the record of the node - a click on it must not fold the node */
  document.querySelectorAll('a.oapfp-uid').forEach((link) => {
    link.addEventListener('click', (event) => { event.stopPropagation(); });
  });

  reveal();
}
