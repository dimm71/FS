#!/usr/bin/perl
 use strict;
 use utf8;
 use Encode;
 use warnings;
 use CGI;
 use DBI;
 use Data::Dump qw(dump);
 $|++;
 my $q = CGI->new;
 my $raw_cdr = $q->param('cdr');
# if (open my $fhlog, '>', '/tmp/cdr.log') {
# printf $fhlog "%s\n", $raw_cdr;
# }
 my @all_fields = qw(caller_id_name caller_id_number destination_number context start_stamp answer_stamp end_stamp duration billsec hangup_cause uuid
 bleg_uuid accountcode read_codec write_codec domain_name operator region recordingfile);
# my @all_fields = qw(calldate clid src dst dcontext channel dstchannel lastapp lastdata duration billsec disposition amaflags accountcode uniqueid userfield
# did operator domain_name region recordingfile);
 my @fields;
 my @values;
 foreach my $field (@all_fields) {
 next unless $raw_cdr =~ m/$field>(.*?)</;
 push @fields, $field;
 push @values, "'" . urldecode($1) . "'";
 }
 my $cdr_line;
 my $query = sprintf(
 "INSERT INTO %s (%s) VALUES (%s);",
 'cdr_test', join(',', @fields), join(',', @values)
 );
 my $db = DBI->connect('DBI:mysql:dbname=freeswitchcdr;host=localhost', 'DB_USER', 'linokuku28')
 or die "DB Connection not made: $DBI::errstr";
 $db->{'mysql_enable_utf8'} = 1;
 $db->do('SET NAMES utf8');
 $db->do($query);
 print $q->header();
 sub urldecode {
 my $url = shift;
 $url =~ s/%([a-fA-F0-9]{2,2})/chr(hex($1))/eg;
 return $url;
 }
