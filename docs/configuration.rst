.. _configuration:

Configuration
=============

.. include:: ./includes/beta-warning.rst

Bitcoin DCA uses `environment variables <https://en.wikipedia.org/wiki/Environment_variable>`_ to configure the inner workings of the tool. An environment variable looks like this: ``SOME_CONFIGURATION_KEY=valuehere``.

Getting Started Template
--------

.. literalinclude:: ../.env.dist
   :language: bash
   :linenos:

Available Configuration
-----------------------

This part of the documentation is split up in generic application settings that decide how the tool should act for Dollar Cost Averaging. The last part is for exchange specific configuration like API keys.

DCA Settings
^^^^^^^^^^^^

WITHDRAW_ADDRESS
""""""""""""""""
You can either use this or ``WITHDRAW_XPUB``. Choosing this one will make the tool withdraw to the same Bitcoin address every time.

**Example**: ``WITHDRAW_ADDRESS=3AT3tf4cVfGRaQ87HpGQppTYmMrb5kpGQb``

WITHDRAW_XPUB
"""""""""""""
You can either use this or ``WITHDRAW_ADDRESS``. Choosing this one will make the tool withdraw to a new receiving address every time a withdrawal is being made by the tool. It'll start at the first address at index 0, so make sure to generate a new account or key when using this method.

**Example**: ``WITHDRAW_XPUB=ypub6Y4RxNmNrdnwdwxERYnXa9rGd4upqeeJ3ixkJQUCQL8UcwYtXj86eXS5fVGU5xsmuuwRp3pKcdci89yiCmA9t2Mhi8cyEDD5P6w2NbfmWqT``

EXCHANGE
""""""""
This configuration value determines which exchange will be used for buys and withdrawals. The default value is BL3P.

Available options: ``bl3p``, ``bitvavo``

**Example**: ``EXCHANGE=bl3p``

Exchange: BL3P
^^^^^^^^^^^^^^

BL3P_PUBLIC_KEY
"""""""""""""""
This is the identifying part of the API key that you created on the BL3P exchange. You can find it there under the name **Identifier Key**.

**Example**: ``BL3P_PUBLIC_KEY=0a12345b-01a1-1a1a-012a-a1bc23ef45bg``

BL3P_PRIVATE_KEY
""""""""""""""""
This is the private part of your API connection to BL3P. It's an encoded secret granting access to your BL3P account.

**Example**: ``BL3P_PRIVATE_KEY=aHR0cHM6Ly93d3cueW91dHViZS5jb20vd2F0Y2g/dj1kUXc0dzlXZ1hjUQ==``

BL3P_API_URL (optional)
"""""""""""""""""""""""
The endpoint where the tool should connect to.

**Example**: ``BL3P_API_URL=https://api.bl3p.eu/1/``

Exchange: Bitvavo
^^^^^^^^^^^^^^

BITVAVO_API_KEY
"""""""""""""""
This is the identifying part of the API key that you created on the Bitvavo exchange.

**Example**: ``BITVAVO_API_KEY=1006e89gd84e8f3a5209b2762d1bbef36eds5e6108e7696f6117556830b0e3dy``

BITVAVO_API_SECRET
""""""""""""""""""
This is the private part of your API connection to Bitvavo.

**Example**: ``BITVAVO_API_SECRET=aHR0cHM6Ly93d3cueW91dHViZS5jb20vd2F0Y2g/dj1kUXc0dzlXZ1hjUQ==``

BITVAVO_API_SECRET (optional)
"""""""""""""""""""""""""""""
The endpoint where the tool should connect to.

**Example**: ``https://api.bitvavo.com/v2/``

Feeding configuration into the DCA tool
---------------------------------------

Using a configuration file
^^^^^^^^^^^^^^^^^^^^^^^^^^
When handling multiple environment variables, things can get messy. For easier management you can create a simple configuration file somewhere on your disk and use that to provide the tool with the correct configuration.

For example, creating a new configuration file in your home directory: ``nano /home/username/.bitcoin-dca``

.. include:: ./includes/finding-home-directory.rst

.. code-block:: bash
   :caption: /home/username/.bitcoin-dca

   BL3P_PUBLIC_KEY=....
   BL3P_PRIVATE_KEY=....
   WITHDRAW_ADDRESS=....

Now, when running the tool you can use ``--env-file`` like this:

.. code-block:: bash
   :caption: Providing configuration with Docker's --env-file

   $ docker run --rm -it --env-file=/home/username/.bitcoin-dca jorijn/bitcoin-dca:latest balance

Using inline arguments
^^^^^^^^^^^^^^^^^^^^^^
For maximum control, you can also feed configuration into the tool like this:

.. note::
   While this gives you more control, it will also allow other people who have access your machine to see the arguments with which you've started the Docker container, thus revealing your API keys.

.. code-block:: bash
   :caption: Providing configuration by specifying each configuration item separately

   $ docker run --rm -it -e BL3P_PUBLIC_KEY=abcd -e BL3P_PRIVATE_KEY=abcd WITHDRAW_ADDRESS=abcd jorijn/bitcoin-dca:latest balance
