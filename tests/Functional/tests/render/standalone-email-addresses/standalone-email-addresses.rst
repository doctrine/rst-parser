Addressing examples taken from https://tools.ietf.org/html/rfc5322.

Appendix A.1.1.  A Message from One Person to Another with Simple Addressing

   From: John Doe <jdoe@machine1.example>
   Sender: Michael Jones <mjones@machine1.example>
   To: Mary Smith <mary@example1.net>
   Subject: Saying Hello
   Date: Fri, 21 Nov 1997 09:55:06 -0600
   Message-ID: <1234@local.machine.example>

   This is a message just to say hello.
   So, "Hello".

Appendix A.1.2.  Different Types of Mailboxes

   From: "Joe Q. Public" <john.q.public@example.com>
   To: Mary Smith <mary@x.test>, jdoe@example.org, Who? <one@y.test>
   Cc: <boss@nil.test>, "Giant; \"Big\" Box" <sysservices@example.net>
   Date: Tue, 1 Jul 2003 10:52:37 +0200
   Message-ID: <5678.21-Nov-1997@example.com>

   Hi everyone.

Appendix A.1.3.  Group Addresses

   From: Pete <pete@silly.example>
   To: A Group:Ed Jones <c@a.test>,joe@where.test,John <jdoe@one1.test>;
   Cc: Undisclosed recipients:;
   Date: Thu, 13 Feb 1969 23:32:54 -0330
   Message-ID: <testabcd.1234@silly.example>

   Testing.

Appendix A.2.  Reply Messages

   To: "Mary Smith: Personal Account" <smith@home.example>
   From: John Doe <jdoe@machine2.example>
   Subject: Re: Saying Hello
   Date: Fri, 21 Nov 1997 11:00:00 -0600
   Message-ID: <abcd.1234@local.machine.test>
   In-Reply-To: <3456@example.net>
   References: <1235@local.machine.example> <3457@example.net>

   This is a reply to your reply.

Appendix A.3.  Resent Messages

   Resent-From: Mary Smith <mary@example2.net>
   Resent-To: Jane Brown <j-brown@other.example>
   Resent-Date: Mon, 24 Nov 1997 14:22:01 -0800
   Resent-Message-ID: <78910@example.net>
   From: John Doe <jdoe@machine3.example>
   To: Mary Smith <mary@example3.net>
   Subject: Saying Hello
   Date: Fri, 21 Nov 1997 09:55:06 -0600
   Message-ID: <1236@local.machine.example>

   This is a message just to say hello.
   So, "Hello".

Appendix A.4.  Messages with Trace Fields

   Received: from x.y.test
      by example.net
      via TCP
      with ESMTP
      id ABC12345
      for <mary@example4.net>;  21 Nov 1997 10:05:43 -0600
   Received: from node.example by x.y.test; 21 Nov 1997 10:01:22 -0600
   From: John Doe <jdoe@node.example>
   To: Mary Smith <mary@example5.net>
   Subject: Saying Hello
   Date: Fri, 21 Nov 1997 09:55:06 -0600
   Message-ID: <1236@local.node.example>

   This is a message just to say hello.
   So, "Hello".

Appendix A.5.  White Space, Comments, and Other Oddities

The addresses here are perfectly legal, according to RFC 5322, but we're not
going to support all these formats.

   From: Pete(A nice \) chap) <pete(his account)@silly.test(his host)>
   To:A Group(Some people)
        :Chris Jones <c@(Chris's host.)public.example>,
            joe@example.org,
     John <jdoe@one2.test> (my dear friend); (the end of the group)
   Cc:(Empty list)(start)Hidden recipients  :(nobody(that I know))  ;
   Date: Thu,
         13
           Feb
             1969
         23:32
                  -0330 (Newfoundland Time)
   Message-ID:              <testabcd.1234@silly.test>

   Testing.


More addresses that should be matched:

    foo+bar@test.email.address
    foo.bar-baz_quux@example-foo.quux
    foo%20bar@example.testareallylongtldinanemailaddress


These should not be matched:

    This is not an email address foo@example.
    Also not one: @example.com text on other side of it
    Another non-address @example foo bar baz
    foo.bar-baz_quux@example-foo_bar.quux
