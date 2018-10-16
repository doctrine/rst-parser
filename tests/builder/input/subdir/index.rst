.. url:: test

Subdirectory
============

Relative `link </to/resource>`_

Absolute `test <http://absolute/>`_

You can click `here <http://google.com>`__ or `here <http://yahoo.com>`__

This is `something`_, and this is again `something`_

This is a :ref:`test anchor <test-anchor>`

This is a :ref:`test subdir reference with anchor </subdir/index#test-subdir-anchor>`

.. _something: http://something.com/

.. _test-anchor:

Reference to the :doc:`/index`

.. _test_reference:

.. _camelCaseReference:

Link to :ref:`the subdir same doc reference <subdir_same_doc_reference>`

.. _subdir_same_doc_reference:

Subdirectory Child
------------------

Test subdirectory child.

Subdirectory Child Level 2
~~~~~~~~~~~~~~~~~~~~~~~~~~

Test subdirectory child level 2.


Subdirectory Child Level 3
**************************

Test subdirectory child level 3.

:doc:`Reference absolute to index </index>`

:doc:`Reference absolute to file </subdir/file>`

:doc:`Reference relative to file <file>`

Reference absolute to the :doc:`/index`

.. include:: /subdir/include.rst.inc

.. include:: include.rst.inc
