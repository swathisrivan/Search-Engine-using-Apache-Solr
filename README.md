# Search-Engine-using-Apache-Solr

Author: Swathi Sridhar(swathisr@usc.edu)

Date: 06/03/2017

--Implementation of a web search engine whose results were retrieved using either Page Rank or Lucene’s in-built boolean vector space model on crawl data obtained using crawler4j for ABCNews Website.

--A NetworkX digraph was represented using an edgelist representation using the outlinks available in each of the downloaded webpages.

--Pagerankfile for all of the crawled webpages was computed using the NetworkX library by feeding the edgeslist graph as the input

--Depending on the type of search(Boolean Vector Space Model / Page Rank Model) specified by the user, the pagerank file is either omitted or made use of while retrieving the search results.

--The Search engine also has a provision for spelling correction using the Norvig's PHP version of spelling corrector whose dictionary is constructed from the dictions available in the crawled and downloaded HTML files.

--The spelling correction feature would retrieve search results for a corrected candidate query if the one or more of the query terms are misspelt and also presents the user an option to search using the original misspelt query.

--The search engine includes the autosuggest feature by making use of solr's inbuilt auto-suggestion feature to provide the user with suggestions for query terms

