@extends('layout.master')

@push('plugin-styles')
  <link href="{{ asset('build/plugins/flag-icons/css/flag-icons.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Icons</a></li>
    <li class="breadcrumb-item active" aria-current="page">Flag Icons</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Flag Icons</h6>
        <p class="text-secondary mb-3">Visit the <a href="https://github.com/lipis/flag-icons" target="_blank"> Official Flag Icons Documentation </a>.</p>                
        <table class="table table-responsive table-bordered mb-3">
          <tbody>
            <tr>
              <td>Example</td>
              <td>Code</td>
            </tr>
            <tr>
              <td><i class="fi fi-us w-30px h-30px"></i></td>
              <td>
                <code>
                  &lt;i class="fi fi-us"&gt;&lt;/i&gt;
                </code>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="container">
          <div class="icons-list row">
            <div class="col-6 col-md-3"><i class="fi fi-ad"></i>AD</div>
            <div class="col-6 col-md-3"><i class="fi fi-ae"></i>AE</div>
            <div class="col-6 col-md-3"><i class="fi fi-af"></i>AF</div>
            <div class="col-6 col-md-3"><i class="fi fi-ag"></i>AG</div>
            <div class="col-6 col-md-3"><i class="fi fi-ai"></i>AU</div>
            <div class="col-6 col-md-3"><i class="fi fi-al"></i>AL</div>
            <div class="col-6 col-md-3"><i class="fi fi-am"></i>AM</div>
            <div class="col-6 col-md-3"><i class="fi fi-ao"></i>AO</div>
            <div class="col-6 col-md-3"><i class="fi fi-aq"></i>AQ</div>
            <div class="col-6 col-md-3"><i class="fi fi-ar"></i>AR</div>
            <div class="col-6 col-md-3"><i class="fi fi-as"></i>AS</div>
            <div class="col-6 col-md-3"><i class="fi fi-at"></i>AT</div>
            <div class="col-6 col-md-3"><i class="fi fi-au"></i>AU</div>
            <div class="col-6 col-md-3"><i class="fi fi-aw"></i>AW</div>
            <div class="col-6 col-md-3"><i class="fi fi-ax"></i>AX</div>
            <div class="col-6 col-md-3"><i class="fi fi-az"></i>AZ</div>
            <div class="col-6 col-md-3"><i class="fi fi-ba"></i>BA</div>
            <div class="col-6 col-md-3"><i class="fi fi-bb"></i>BB</div>
            <div class="col-6 col-md-3"><i class="fi fi-bd"></i>BD</div>
            <div class="col-6 col-md-3"><i class="fi fi-be"></i>BE</div>
            <div class="col-6 col-md-3"><i class="fi fi-bf"></i>BF</div>
            <div class="col-6 col-md-3"><i class="fi fi-bg"></i>BG</div>
            <div class="col-6 col-md-3"><i class="fi fi-bh"></i>BH</div>
            <div class="col-6 col-md-3"><i class="fi fi-bi"></i>BI</div>
            <div class="col-6 col-md-3"><i class="fi fi-bj"></i>BJ</div>
            <div class="col-6 col-md-3"><i class="fi fi-bl"></i>BL</div>
            <div class="col-6 col-md-3"><i class="fi fi-bm"></i>BM</div>
            <div class="col-6 col-md-3"><i class="fi fi-bn"></i>BN</div>
            <div class="col-6 col-md-3"><i class="fi fi-bo"></i>BO</div>
            <div class="col-6 col-md-3"><i class="fi fi-bq"></i>BQ</div>
            <div class="col-6 col-md-3"><i class="fi fi-br"></i>BR</div>
            <div class="col-6 col-md-3"><i class="fi fi-bs"></i>BS</div>
            <div class="col-6 col-md-3"><i class="fi fi-bt"></i>BT</div>
            <div class="col-6 col-md-3"><i class="fi fi-bv"></i>BV</div>
            <div class="col-6 col-md-3"><i class="fi fi-bw"></i>BW</div>
            <div class="col-6 col-md-3"><i class="fi fi-by"></i>BY</div>
            <div class="col-6 col-md-3"><i class="fi fi-bz"></i>BZ</div>
            <div class="col-6 col-md-3"><i class="fi fi-ca"></i>CA</div>
            <div class="col-6 col-md-3"><i class="fi fi-cc"></i>CC</div>
            <div class="col-6 col-md-3"><i class="fi fi-cd"></i>CD</div>
            <div class="col-6 col-md-3"><i class="fi fi-cf"></i>CF</div>
            <div class="col-6 col-md-3"><i class="fi fi-cg"></i>CG</div>
            <div class="col-6 col-md-3"><i class="fi fi-ch"></i>CH</div>
            <div class="col-6 col-md-3"><i class="fi fi-ci"></i>CI</div>
            <div class="col-6 col-md-3"><i class="fi fi-ck"></i>CK</div>
            <div class="col-6 col-md-3"><i class="fi fi-cl"></i>CL</div>
            <div class="col-6 col-md-3"><i class="fi fi-cm"></i>CM</div>
            <div class="col-6 col-md-3"><i class="fi fi-cn"></i>CN</div>
            <div class="col-6 col-md-3"><i class="fi fi-co"></i>CO</div>
            <div class="col-6 col-md-3"><i class="fi fi-cr"></i>CR</div>
            <div class="col-6 col-md-3"><i class="fi fi-cu"></i>CU</div>
            <div class="col-6 col-md-3"><i class="fi fi-cv"></i>CV</div>
            <div class="col-6 col-md-3"><i class="fi fi-cw"></i>CW</div>
            <div class="col-6 col-md-3"><i class="fi fi-cx"></i>CX</div>
            <div class="col-6 col-md-3"><i class="fi fi-cy"></i>CY</div>
            <div class="col-6 col-md-3"><i class="fi fi-cz"></i>CZ</div>
            <div class="col-6 col-md-3"><i class="fi fi-de"></i>DE</div>
            <div class="col-6 col-md-3"><i class="fi fi-dj"></i>DJ</div>
            <div class="col-6 col-md-3"><i class="fi fi-dk"></i>DK</div>
            <div class="col-6 col-md-3"><i class="fi fi-dm"></i>DM</div>
            <div class="col-6 col-md-3"><i class="fi fi-do"></i>DO</div>
            <div class="col-6 col-md-3"><i class="fi fi-dz"></i>DZ</div>
            <div class="col-6 col-md-3"><i class="fi fi-ec"></i>EC</div>
            <div class="col-6 col-md-3"><i class="fi fi-ee"></i>EE</div>
            <div class="col-6 col-md-3"><i class="fi fi-eg"></i>EG</div>
            <div class="col-6 col-md-3"><i class="fi fi-eh"></i>EH</div>
            <div class="col-6 col-md-3"><i class="fi fi-er"></i>ER</div>
            <div class="col-6 col-md-3"><i class="fi fi-es"></i>ES</div>
            <div class="col-6 col-md-3"><i class="fi fi-et"></i>ET</div>
            <div class="col-6 col-md-3"><i class="fi fi-fi"></i>FI</div>
            <div class="col-6 col-md-3"><i class="fi fi-fj"></i>FJ</div>
            <div class="col-6 col-md-3"><i class="fi fi-fk"></i>FK</div>
            <div class="col-6 col-md-3"><i class="fi fi-fm"></i>FM</div>
            <div class="col-6 col-md-3"><i class="fi fi-fo"></i>FO</div>
            <div class="col-6 col-md-3"><i class="fi fi-fr"></i>FR</div>
            <div class="col-6 col-md-3"><i class="fi fi-ga"></i>GA</div>
            <div class="col-6 col-md-3"><i class="fi fi-gb"></i>GB</div>
            <div class="col-6 col-md-3"><i class="fi fi-gd"></i>GD</div>
            <div class="col-6 col-md-3"><i class="fi fi-ge"></i>GE</div>
            <div class="col-6 col-md-3"><i class="fi fi-gf"></i>GF</div>
            <div class="col-6 col-md-3"><i class="fi fi-gg"></i>GG</div>
            <div class="col-6 col-md-3"><i class="fi fi-gh"></i>GH</div>
            <div class="col-6 col-md-3"><i class="fi fi-gi"></i>GI</div>
            <div class="col-6 col-md-3"><i class="fi fi-gl"></i>GL</div>
            <div class="col-6 col-md-3"><i class="fi fi-gm"></i>GM</div>
            <div class="col-6 col-md-3"><i class="fi fi-gn"></i>GN</div>
            <div class="col-6 col-md-3"><i class="fi fi-gp"></i>GP</div>
            <div class="col-6 col-md-3"><i class="fi fi-gq"></i>GQ</div>
            <div class="col-6 col-md-3"><i class="fi fi-gr"></i>GR</div>
            <div class="col-6 col-md-3"><i class="fi fi-gs"></i>GS</div>
            <div class="col-6 col-md-3"><i class="fi fi-gt"></i>GT</div>
            <div class="col-6 col-md-3"><i class="fi fi-gu"></i>GU</div>
            <div class="col-6 col-md-3"><i class="fi fi-gw"></i>GW</div>
            <div class="col-6 col-md-3"><i class="fi fi-gy"></i>GY</div>
            <div class="col-6 col-md-3"><i class="fi fi-hk"></i>HK</div>
            <div class="col-6 col-md-3"><i class="fi fi-hm"></i>HM</div>
            <div class="col-6 col-md-3"><i class="fi fi-hn"></i>HN</div>
            <div class="col-6 col-md-3"><i class="fi fi-hr"></i>HR</div>
            <div class="col-6 col-md-3"><i class="fi fi-ht"></i>HT</div>
            <div class="col-6 col-md-3"><i class="fi fi-hu"></i>HU</div>
            <div class="col-6 col-md-3"><i class="fi fi-id"></i>ID</div>
            <div class="col-6 col-md-3"><i class="fi fi-ie"></i>IE</div>
            <div class="col-6 col-md-3"><i class="fi fi-il"></i>IL</div>
            <div class="col-6 col-md-3"><i class="fi fi-im"></i>IM</div>
            <div class="col-6 col-md-3"><i class="fi fi-in"></i>IN</div>
            <div class="col-6 col-md-3"><i class="fi fi-io"></i>IO</div>
            <div class="col-6 col-md-3"><i class="fi fi-iq"></i>IQ</div>
            <div class="col-6 col-md-3"><i class="fi fi-ir"></i>IR</div>
            <div class="col-6 col-md-3"><i class="fi fi-is"></i>IS</div>
            <div class="col-6 col-md-3"><i class="fi fi-it"></i>IT</div>
            <div class="col-6 col-md-3"><i class="fi fi-je"></i>JE</div>
            <div class="col-6 col-md-3"><i class="fi fi-jm"></i>JM</div>
            <div class="col-6 col-md-3"><i class="fi fi-jo"></i>JO</div>
            <div class="col-6 col-md-3"><i class="fi fi-jp"></i>JP</div>
            <div class="col-6 col-md-3"><i class="fi fi-ke"></i>KE</div>
            <div class="col-6 col-md-3"><i class="fi fi-kg"></i>KG</div>
            <div class="col-6 col-md-3"><i class="fi fi-kh"></i>KH</div>
            <div class="col-6 col-md-3"><i class="fi fi-ki"></i>KI</div>
            <div class="col-6 col-md-3"><i class="fi fi-km"></i>KM</div>
            <div class="col-6 col-md-3"><i class="fi fi-kn"></i>KN</div>
            <div class="col-6 col-md-3"><i class="fi fi-kp"></i>KP</div>
            <div class="col-6 col-md-3"><i class="fi fi-kr"></i>KR</div>
            <div class="col-6 col-md-3"><i class="fi fi-kw"></i>KW</div>
            <div class="col-6 col-md-3"><i class="fi fi-ky"></i>KY</div>
            <div class="col-6 col-md-3"><i class="fi fi-kz"></i>KZ</div>
            <div class="col-6 col-md-3"><i class="fi fi-la"></i>LA</div>
            <div class="col-6 col-md-3"><i class="fi fi-lb"></i>LB</div>
            <div class="col-6 col-md-3"><i class="fi fi-lc"></i>LC</div>
            <div class="col-6 col-md-3"><i class="fi fi-li"></i>LI</div>
            <div class="col-6 col-md-3"><i class="fi fi-lk"></i>LK</div>
            <div class="col-6 col-md-3"><i class="fi fi-lr"></i>LR</div>
            <div class="col-6 col-md-3"><i class="fi fi-ls"></i>LS</div>
            <div class="col-6 col-md-3"><i class="fi fi-lt"></i>LT</div>
            <div class="col-6 col-md-3"><i class="fi fi-lu"></i>LU</div>
            <div class="col-6 col-md-3"><i class="fi fi-lv"></i>LV</div>
            <div class="col-6 col-md-3"><i class="fi fi-ly"></i>LY</div>
            <div class="col-6 col-md-3"><i class="fi fi-ma"></i>MA</div>
            <div class="col-6 col-md-3"><i class="fi fi-mc"></i>MC</div>
            <div class="col-6 col-md-3"><i class="fi fi-md"></i>MD</div>
            <div class="col-6 col-md-3"><i class="fi fi-me"></i>ME</div>
            <div class="col-6 col-md-3"><i class="fi fi-mf"></i>MF</div>
            <div class="col-6 col-md-3"><i class="fi fi-mg"></i>MG</div>
            <div class="col-6 col-md-3"><i class="fi fi-mh"></i>MH</div>
            <div class="col-6 col-md-3"><i class="fi fi-mk"></i>MK</div>
            <div class="col-6 col-md-3"><i class="fi fi-ml"></i>ML</div>
            <div class="col-6 col-md-3"><i class="fi fi-mm"></i>MM</div>
            <div class="col-6 col-md-3"><i class="fi fi-mn"></i>MN</div>
            <div class="col-6 col-md-3"><i class="fi fi-mo"></i>MO</div>
            <div class="col-6 col-md-3"><i class="fi fi-mp"></i>MP</div>
            <div class="col-6 col-md-3"><i class="fi fi-mq"></i>MQ</div>
            <div class="col-6 col-md-3"><i class="fi fi-mr"></i>MR</div>
            <div class="col-6 col-md-3"><i class="fi fi-ms"></i>MS</div>
            <div class="col-6 col-md-3"><i class="fi fi-mt"></i>MT</div>
            <div class="col-6 col-md-3"><i class="fi fi-mu"></i>MU</div>
            <div class="col-6 col-md-3"><i class="fi fi-mv"></i>MV</div>
            <div class="col-6 col-md-3"><i class="fi fi-mw"></i>MW</div>
            <div class="col-6 col-md-3"><i class="fi fi-mx"></i>MX</div>
            <div class="col-6 col-md-3"><i class="fi fi-my"></i>MY</div>
            <div class="col-6 col-md-3"><i class="fi fi-mz"></i>MZ</div>
            <div class="col-6 col-md-3"><i class="fi fi-na"></i>NA</div>
            <div class="col-6 col-md-3"><i class="fi fi-nc"></i>NC</div>
            <div class="col-6 col-md-3"><i class="fi fi-ne"></i>NE</div>
            <div class="col-6 col-md-3"><i class="fi fi-nf"></i>NF</div>
            <div class="col-6 col-md-3"><i class="fi fi-ng"></i>NG</div>
            <div class="col-6 col-md-3"><i class="fi fi-ni"></i>NI</div>
            <div class="col-6 col-md-3"><i class="fi fi-nl"></i>NL</div>
            <div class="col-6 col-md-3"><i class="fi fi-no"></i>NO</div>
            <div class="col-6 col-md-3"><i class="fi fi-np"></i>NP</div>
            <div class="col-6 col-md-3"><i class="fi fi-nr"></i>NR</div>
            <div class="col-6 col-md-3"><i class="fi fi-nu"></i>NU</div>
            <div class="col-6 col-md-3"><i class="fi fi-nz"></i>NZ</div>
            <div class="col-6 col-md-3"><i class="fi fi-om"></i>OM</div>
            <div class="col-6 col-md-3"><i class="fi fi-pa"></i>PA</div>
            <div class="col-6 col-md-3"><i class="fi fi-pe"></i>PE</div>
            <div class="col-6 col-md-3"><i class="fi fi-pf"></i>PF</div>
            <div class="col-6 col-md-3"><i class="fi fi-pg"></i>PG</div>
            <div class="col-6 col-md-3"><i class="fi fi-ph"></i>PH</div>
            <div class="col-6 col-md-3"><i class="fi fi-pk"></i>PK</div>
            <div class="col-6 col-md-3"><i class="fi fi-pl"></i>PL</div>
            <div class="col-6 col-md-3"><i class="fi fi-pm"></i>PM</div>
            <div class="col-6 col-md-3"><i class="fi fi-pn"></i>PN</div>
            <div class="col-6 col-md-3"><i class="fi fi-pr"></i>PR</div>
            <div class="col-6 col-md-3"><i class="fi fi-ps"></i>PS</div>
            <div class="col-6 col-md-3"><i class="fi fi-pt"></i>PT</div>
            <div class="col-6 col-md-3"><i class="fi fi-pw"></i>PW</div>
            <div class="col-6 col-md-3"><i class="fi fi-py"></i>PY</div>
            <div class="col-6 col-md-3"><i class="fi fi-qa"></i>QA</div>
            <div class="col-6 col-md-3"><i class="fi fi-re"></i>RE</div>
            <div class="col-6 col-md-3"><i class="fi fi-ro"></i>RO</div>
            <div class="col-6 col-md-3"><i class="fi fi-rs"></i>RS</div>
            <div class="col-6 col-md-3"><i class="fi fi-ru"></i>RU</div>
            <div class="col-6 col-md-3"><i class="fi fi-rw"></i>RW</div>
            <div class="col-6 col-md-3"><i class="fi fi-sa"></i>SA</div>
            <div class="col-6 col-md-3"><i class="fi fi-sb"></i>SB</div>
            <div class="col-6 col-md-3"><i class="fi fi-sc"></i>SC</div>
            <div class="col-6 col-md-3"><i class="fi fi-sd"></i>SD</div>
            <div class="col-6 col-md-3"><i class="fi fi-se"></i>SE</div>
            <div class="col-6 col-md-3"><i class="fi fi-sg"></i>SG</div>
            <div class="col-6 col-md-3"><i class="fi fi-sh"></i>SH</div>
            <div class="col-6 col-md-3"><i class="fi fi-si"></i>SI</div>
            <div class="col-6 col-md-3"><i class="fi fi-sj"></i>SJ</div>
            <div class="col-6 col-md-3"><i class="fi fi-sk"></i>SK</div>
            <div class="col-6 col-md-3"><i class="fi fi-sl"></i>SL</div>
            <div class="col-6 col-md-3"><i class="fi fi-sm"></i>SM</div>
            <div class="col-6 col-md-3"><i class="fi fi-sn"></i>SN</div>
            <div class="col-6 col-md-3"><i class="fi fi-so"></i>SO</div>
            <div class="col-6 col-md-3"><i class="fi fi-sr"></i>SR</div>
            <div class="col-6 col-md-3"><i class="fi fi-ss"></i>SS</div>
            <div class="col-6 col-md-3"><i class="fi fi-st"></i>ST</div>
            <div class="col-6 col-md-3"><i class="fi fi-sv"></i>SV</div>
            <div class="col-6 col-md-3"><i class="fi fi-sx"></i>SX</div>
            <div class="col-6 col-md-3"><i class="fi fi-sy"></i>SY</div>
            <div class="col-6 col-md-3"><i class="fi fi-sz"></i>SZ</div>
            <div class="col-6 col-md-3"><i class="fi fi-tc"></i>TC</div>
            <div class="col-6 col-md-3"><i class="fi fi-td"></i>TD</div>
            <div class="col-6 col-md-3"><i class="fi fi-tf"></i>TF</div>
            <div class="col-6 col-md-3"><i class="fi fi-tg"></i>TG</div>
            <div class="col-6 col-md-3"><i class="fi fi-th"></i>TH</div>
            <div class="col-6 col-md-3"><i class="fi fi-tj"></i>TJ</div>
            <div class="col-6 col-md-3"><i class="fi fi-tk"></i>TK</div>
            <div class="col-6 col-md-3"><i class="fi fi-tl"></i>TL</div>
            <div class="col-6 col-md-3"><i class="fi fi-tm"></i>TM</div>
            <div class="col-6 col-md-3"><i class="fi fi-tn"></i>TN</div>
            <div class="col-6 col-md-3"><i class="fi fi-to"></i>TO</div>
            <div class="col-6 col-md-3"><i class="fi fi-tr"></i>TR</div>
            <div class="col-6 col-md-3"><i class="fi fi-tt"></i>TT</div>
            <div class="col-6 col-md-3"><i class="fi fi-tv"></i>TV</div>
            <div class="col-6 col-md-3"><i class="fi fi-tw"></i>TW</div>
            <div class="col-6 col-md-3"><i class="fi fi-tz"></i>TZ</div>
            <div class="col-6 col-md-3"><i class="fi fi-ua"></i>UA</div>
            <div class="col-6 col-md-3"><i class="fi fi-ug"></i>UG</div>
            <div class="col-6 col-md-3"><i class="fi fi-um"></i>UM</div>
            <div class="col-6 col-md-3"><i class="fi fi-us"></i>US</div>
            <div class="col-6 col-md-3"><i class="fi fi-uy"></i>UY</div>
            <div class="col-6 col-md-3"><i class="fi fi-uz"></i>UZ</div>
            <div class="col-6 col-md-3"><i class="fi fi-va"></i>VA</div>
            <div class="col-6 col-md-3"><i class="fi fi-vc"></i>VC</div>
            <div class="col-6 col-md-3"><i class="fi fi-ve"></i>VE</div>
            <div class="col-6 col-md-3"><i class="fi fi-vg"></i>VG</div>
            <div class="col-6 col-md-3"><i class="fi fi-vi"></i>VI</div>
            <div class="col-6 col-md-3"><i class="fi fi-vn"></i>VN</div>
            <div class="col-6 col-md-3"><i class="fi fi-vu"></i>VU</div>
            <div class="col-6 col-md-3"><i class="fi fi-wf"></i>WF</div>
            <div class="col-6 col-md-3"><i class="fi fi-ws"></i>WS</div>
            <div class="col-6 col-md-3"><i class="fi fi-ye"></i>YE</div>
            <div class="col-6 col-md-3"><i class="fi fi-yt"></i>YT</div>
            <div class="col-6 col-md-3"><i class="fi fi-za"></i>ZA</div>
            <div class="col-6 col-md-3"><i class="fi fi-zm"></i>ZM</div>
            <div class="col-6 col-md-3"><i class="fi fi-zw"></i>ZW</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection