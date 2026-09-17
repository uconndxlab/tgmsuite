<h3>Edit Field</h3>
<form method="POST" action="/fields/{{ $row->id ?? 0 }}">
  @csrf

  <div class="row">
    <div class="col-12">
      <!-- accordion #fieldInfo -->
      <div class="accordion-borderless" id="fieldInfo">
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#fieldInfo-meta"
              aria-expanded="true">
              Field Info
            </button>
          </h2>
          <div id="fieldInfo-meta" class="accordion-collapse collapse show" data-bs-parent="#fieldInfo">
            <div class="accordion-body">
              <div class="row form-group">
                <div class="mb-3">
                  <label for="name" class="form-label">Field Name (this is the only required field):</label>
                  <input required type="text" id="name" class="form-control" name="name" value="{{ old('name', $row->name ?? '') }}">
                </div>

                <div class="mb-3">
                  <label for="address" class="form-label">Address:</label>
                  <input type="text" id="address" class="form-control" name="address" value="{{ old('address', $row->address ?? '') }}">
                </div>

                <div class="row">
                  <div class="mb-3 col">
                    <label for="city" class="form-label">City:</label>
                    <input type="text" id="city" class="form-control" name="city" value="{{ old('city', $row->city ?? '') }}">
                  </div>

                  <div class="mb-3 col">
                    <label for="state" class="form-label">State:</label>
                    <input type="text" id="state" class="form-control" name="state" value="{{ old('state', $row->state ?? '') }}">
                  </div>

                  <div class="mb-3 col">
                    <label for="zip" class="form-label">Zip:</label>
                    <input type="text" id="zip" class="form-control" name="zip" value="{{ old('zip', $row->zip ?? '') }}">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- field usage information accordion item -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingTwo">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
              data-bs-target="#fieldUsage-meta" aria-expanded="false">
              Field Usage
            </button>
          </h2>
          <div id="fieldUsage-meta" class="accordion-collapse collapse" data-bs-parent="#fieldInfo">
            <div class="accordion-body">
              <div class="row">
                <div class="mb-3">
                  <label for="sports_played" class="form-label">Sports Played:</label>
                  <div class="form-check">
                    <input id="baseball" class="form-check-input" type="checkbox" name="sports_played[]" value="baseball" @if(str_contains($row->sports_played ?? '', 'baseball')) checked @endif>
                    <label class="form-check-label" for="baseball">Baseball</label>
                  </div>
                  <div class="form-check">
                    <input id="softball" class="form-check-input" type="checkbox" name="sports_played[]" value="softball" @if(str_contains($row->sports_played ?? '', 'softball')) checked @endif>
                    <label class="form-check-label" for="softball">Softball</label>
                  </div>
                  <div class="form-check">
                    <input id="soccer" class="form-check-input" type="checkbox" name="sports_played[]" value="soccer" @if(str_contains($row->sports_played ?? '', 'soccer')) checked @endif>
                    <label class="form-check-label" for="soccer">Soccer</label>
                  </div>
                  <div class="form-check">
                    <input id="football" class="form-check-input" type="checkbox" name="sports_played[]" value="football" @if(str_contains($row->sports_played ?? '', 'football')) checked @endif>
                    <label class="form-check-label" for="football">Football</label>
                  </div>
                  <div class="form-check">
                    <input id="lacrosse" class="form-check-input" type="checkbox" name="sports_played[]" value="lacrosse" @if(str_contains($row->sports_played ?? '', 'lacrosse')) checked @endif>
                    <label class="form-check-label" for="lacrosse">Lacrosse</label>
                  </div>
                  <div class="form-check">
                    <input id="rugby" class="form-check-input" type="checkbox" name="sports_played[]" value="rugby" @if(str_contains($row->sports_played ?? '', 'rugby')) checked @endif>
                    <label class="form-check-label" for="rugby">Rugby</label>
                  </div>
                  <div class="form-check">
                    <input id="field_hockey" class="form-check-input" type="checkbox" name="sports_played[]" value="field hockey" @if(str_contains($row->sports_played ?? '', 'field hockey')) checked @endif>
                    <label class="form-check-label" for="field_hockey">Field Hockey</label>
                  </div>
                  <div class="form-check">
                    <input id="pe_class" class="form-check-input" type="checkbox" name="sports_played[]" value="pe_class" @if(str_contains($row->sports_played ?? '', 'pe_class')) checked @endif>
                    <label class="form-check-label" for="pe_class">PE Class/Recreational use</label>
                  </div>
                </div>

                <!-- traffic events per week -->
                <div class="mb-3">
                  <label for="traffic_events_per_week" class="form-label">Traffic Events Per Week:</label>
                  <select id="traffic_events_per_week" name="traffic_events_per_week" class="form-select form-control">
                    <option>0-2 per week</option>
                    <option>3-5 per week</option>
                    <option>6+ per week</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- establishment information accordion item -->
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
              data-bs-target="#establishment-meta" aria-expanded="false">
              Establishment Info
            </button>
          </h2>
          <div id="establishment-meta" class="accordion-collapse collapse" data-bs-parent="#fieldInfo">
            <div class="accordion-body">
              <div class="row">
                <div class="form-group mb-3">
                  <label for="turfgrass_species_present">Turfgrass Species Present:</label>
                  <div class="form-check">
                    <input id="tall_fescue" class="form-check-input" type="checkbox" name="turfgrass_species_present[]" value="tall fescue" @if(str_contains($row->turfgrass_species_present ?? '', 'tall fescue')) checked @endif>
                    <label class="form-check-label" for="tall_fescue">Tall Fescue</label>
                  </div>
                  <div class="form-check">
                    <input id="perennial_ryegrass" class="form-check-input" type="checkbox" name="turfgrass_species_present[]" value="perennial ryegrass" @if(str_contains($row->turfgrass_species_present ?? '', 'perennial ryegrass')) checked @endif>
                    <label class="form-check-label" for="perennial_ryegrass">Perennial Ryegrass</label>
                  </div>
                  <div class="form-check">
                    <input id="kentucky_bluegrass" class="form-check-input" type="checkbox" name="turfgrass_species_present[]" value="kentucky bluegrass" @if(str_contains($row->turfgrass_species_present ?? '', 'kentucky bluegrass')) checked @endif>
                    <label class="form-check-label" for="kentucky_bluegrass">Kentucky Bluegrass</label>
                  </div>
                  <div class="form-check">
                    <input id="fine_fescue" class="form-check-input" type="checkbox" name="turfgrass_species_present[]" value="fine fescue" @if(str_contains($row->turfgrass_species_present ?? '', 'fine fescue')) checked @endif>
                    <label class="form-check-label" for="fine_fescue">Fine Fescue</label>
                  </div>
                  <div class="form-check">
                    <input id="other" class="form-check-input" type="checkbox" name="turfgrass_species_present[]" value="other" @if(str_contains($row->turfgrass_species_present ?? '', 'other')) checked @endif>
                    <label class="form-check-label" for="other">Other</label>
                  </div>

                  <div class="row mt-3">
                    <div class="mb-3 col">
                      <label for="establishment_method" class="form-label">Establishment Method:</label>
                      <select class="form-select form-control" name="establishment_method">
                        <option value="seed" @if(str_contains($row->establishment_method ?? '', 'seed')) selected @endif>Seed</option>
                        <option value="sod" @if(str_contains($row->establishment_method ?? '', 'sod')) selected @endif>Sod</option>
                      </select>
                    </div>

                    <div class="mb-3 col">
                      <label for="establishment_date" class="form-label">Establishment Date:</label>
                      <input type="date" id="establishment_date" class="form-control" name="establishment_date"
                        value="{{ $row->establishment_date ?? '' }}">
                    </div>

                    <div class="mb-3 col">
                      <label for="shade_or_sun" class="form-label">Shade or Sun:</label>
                      <select id="shade_or_sun" class="form-select form-control" name="shade_or_sun">
                        <option @if(empty($row->shade_or_sun) || str_contains($row->shade_or_sun, 'sun')) selected @endif value="sun">Full Sun</option>
                        <option @if(str_contains($row->shade_or_sun ?? '', 'shade')) selected @endif value="shade">Partial Shade</option>
                      </select>
                    </div>

                    <div class="mb-3 col {{ ($row->shade_or_sun ?? '') === 'shade' ? '' : 'invisible' }}" id="percent_shade_wrap">
                      <label for="percent_shade" class="form-label">Percent Shade:</label>
                      <input id="percent_shade" type="text" class="form-control" name="percent_shade"
                        value="{{ $row->percent_shade ?? 0 }}">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- renovation information -->
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
              data-bs-target="#renovation-meta" aria-expanded="false">
              Renovation Info
            </button>
          </h2>
          <div id="renovation-meta" class="accordion-collapse collapse" data-bs-parent="#fieldInfo">
            <div class="accordion-body">
              <div class="row">
                <div class="form-group mb-3">
                  <div class="mb-3">
                    <label for="percent_renovated" class="form-label">Percent Renovated:</label>
                    <input type="text" id="percent_renovated" class="form-control" name="percent_renovated"
                      value="{{ $row->percent_renovated ?? '' }}">
                  </div>

                  <div class="mb-3">
                    <label for="renovation_date" class="form-label">Renovation Date:</label>
                    <input type="date" id="renovation_date" class="form-control" name="renovation_date"
                      value="{{ $row->renovation_date ?? '' }}">
                  </div>

                  <div class="mb-3">
                    <label for="renovation_type" class="form-label">Renovation Type:</label>
                    <input type="text" id="renovation_type" class="form-control" name="renovation_type"
                      value="{{ $row->renovation_type ?? '' }}">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- soil details -->
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
              data-bs-target="#soil-meta" aria-expanded="false">
              Soil Details
            </button>
          </h2>
          <div id="soil-meta" class="accordion-collapse collapse" data-bs-parent="#fieldInfo">
            <div class="accordion-body">
              <div class="row">
                <div class="form-group mb-3">
                  <label for="soil_texture">Soil Texture:</label>
                  <div class="form-check">
                    <input id="sand" class="form-check-input" type="checkbox" name="soil_texture[]" value="sand" @if(str_contains($row->soil_texture ?? '', 'sand')) checked @endif>
                    <label class="form-check-label" for="sand">Sand</label>
                  </div>
                  <div class="form-check">
                    <input id="silt" class="form-check-input" type="checkbox" name="soil_texture[]" value="silt" @if(str_contains($row->soil_texture ?? '', 'silt')) checked @endif>
                    <label class="form-check-label" for="silt">Silt</label>
                  </div>
                  <div class="form-check">
                    <input id="clay" class="form-check-input" type="checkbox" name="soil_texture[]" value="clay" @if(str_contains($row->soil_texture ?? '', 'clay')) checked @endif>
                    <label class="form-check-label" for="clay">Clay</label>
                  </div>
                  <div class="form-check">
                    <input id="loam" class="form-check-input" type="checkbox" name="soil_texture[]" value="loam" @if(str_contains($row->soil_texture ?? '', 'loam')) checked @endif>
                    <label class="form-check-label" for="loam">Loam</label>
                  </div>
                  <div class="form-check">
                    <input id="sandy_loam" class="form-check-input" type="checkbox" name="soil_texture[]" value="sandy loam" @if(str_contains($row->soil_texture ?? '', 'sandy loam')) checked @endif>
                    <label class="form-check-label" for="sandy_loam">Sandy Loam</label>
                  </div>
                  <div class="form-check">
                    <input id="soil_other" class="form-check-input" type="checkbox" name="soil_texture[]" value="other" @if(str_contains($row->soil_texture ?? '', 'other')) checked @endif>
                    <label class="form-check-label" for="soil_other">Other</label>
                  </div>

                  <div class="row mt-3">
                    <div class="mb-3 col">
                      <label for="soil_depth" class="form-label">Soil Depth:</label>
                      <input type="text" id="soil_depth" class="form-control" name="soil_depth" value="{{ $row->soil_depth ?? '' }}">
                    </div>

                    <div class="mb-3 col">
                      <label for="soil_condition" class="form-label">Soil Condition:</label>
                      <input type="text" id="soil_condition" class="form-control" name="soil_condition" value="{{ $row->soil_condition ?? '' }}">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- mowing practices -->
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
              data-bs-target="#mowing-meta" aria-expanded="false">
              Mowing Practices
            </button>
          </h2>
          <div id="mowing-meta" class="accordion-collapse collapse">
            <div class="accordion-body">
              <div class="row">
                <div class="form-group mb-3">
                  <div class="mb-3">
                    <label for="pgrs_used" class="form-label">PGRs Used:</label>
                    <select id="pgrs_used" class="form-select form-control" name="pgrs_used">
                      <option value="no" @if(str_contains($row->pgrs_used ?? '', 'no')) selected @endif>No</option>
                      <option value="yes" @if(str_contains($row->pgrs_used ?? '', 'yes')) selected @endif>Yes</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="mowing_method" class="form-label">Mowing Method:</label>
                    <select class="form-select form-control" name="mowing_method">
                      <option value="rotary" @if(str_contains($row->mowing_method ?? '', 'rotary')) selected @endif>Rotary</option>
                      <option value="reel" @if(str_contains($row->mowing_method ?? '', 'reel')) selected @endif>Reel</option>
                      <option value="autonomous" @if(str_contains($row->mowing_method ?? '', 'autonomous')) selected @endif>Autonomous</option>
                      <option value="other" @if(str_contains($row->mowing_method ?? '', 'other')) selected @endif>Other</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="mowing_height" class="form-label">Mowing Height:</label>
                    <select class="form-select form-control" name="mowing_height">
                      <option value="1-1.75" @if(str_contains($row->mowing_height ?? '', '1-1.75')) selected @endif>1-1.75"</option>
                      <option value="2-2.25" @if(str_contains($row->mowing_height ?? '', '2-2.25')) selected @endif>2-2.25"</option>
                      <option value="2.5" @if(str_contains($row->mowing_height ?? '', '2.5')) selected @endif>2.5"</option>
                      <option value="2.75-3" @if(str_contains($row->mowing_height ?? '', '2.75-3')) selected @endif>2.75-3"</option>
                      <option value="3.5-4" @if(str_contains($row->mowing_height ?? '', '3.5-4')) selected @endif>3.5-4"</option>
                      <option value="4+" @if(str_contains($row->mowing_height ?? '', '4+')) selected @endif>4+"</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="clippings_collected" class="form-label">Clippings Collected:</label>
                    <select class="form-select form-control" name="clippings_collected">
                      <option value="no" @if(str_contains($row->clippings_collected ?? '', 'no')) selected @endif>No</option>
                      <option value="yes" @if(str_contains($row->clippings_collected ?? '', 'yes')) selected @endif>Yes</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- irrigation practices -->
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
              data-bs-target="#irrigation-meta" aria-expanded="false">
              Irrigation Practices
            </button>
          </h2>
          <div id="irrigation-meta" class="accordion-collapse collapse">
            <div class="accordion-body">
              <div class="row">
                <div class="form-group mb-3">
                  <div class="mb-3">
                    <label for="irrigation_system" class="form-label">Irrigation:</label>
                    <select class="form-select form-control" name="irrigation_system">
                      <option value="no" @if(str_contains($row->irrigation_system ?? '', 'no')) selected @endif>No</option>
                      <option value="yes" @if(str_contains($row->irrigation_system ?? '', 'yes')) selected @endif>Yes</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="water_source" class="form-label">Water Source:</label>
                    <div class="form-check">
                      <input id="well" class="form-check-input" type="checkbox" name="water_source[]" value="well" @if(str_contains($row->water_source ?? '', 'well')) checked @endif>
                      <label class="form-check-label" for="well">Well</label>
                    </div>
                    <div class="form-check">
                      <input id="pond" class="form-check-input" type="checkbox" name="water_source[]" value="pond" @if(str_contains($row->water_source ?? '', 'pond')) checked @endif>
                      <label class="form-check-label" for="pond">Pond</label>
                    </div>
                    <div class="form-check">
                      <input id="town_water" class="form-check-input" type="checkbox" name="water_source[]" value="town water" @if(str_contains($row->water_source ?? '', 'town water')) checked @endif>
                      <label class="form-check-label" for="town_water">Town Water</label>
                    </div>
                    <div class="form-check">
                      <input id="greywater_system" class="form-check-input" type="checkbox" name="water_source[]" value="greywater system" @if(str_contains($row->water_source ?? '', 'greywater system')) checked @endif>
                      <label class="form-check-label" for="greywater_system">Greywater System</label>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="irrigation_frequency" class="form-label">Irrigation Frequency:</label>
                    <select class="form-select form-control" name="irrigation_frequency">
                      <option value="5+x/week" @if(str_contains($row->irrigation_frequency ?? '', '5+x/week')) selected @endif>5+x/week</option>
                      <option value="2-4x/week" @if(str_contains($row->irrigation_frequency ?? '', '2-4x/week')) selected @endif>2-4x/week</option>
                      <option value="1x/week" @if(str_contains($row->irrigation_frequency ?? '', '1x/week')) selected @endif>1x/week</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="portable_system" class="form-label">System Used:</label>
                    <select class="form-select form-control" name="portable_system">
                      <option value="none" @if(str_contains($row->portable_system ?? '', 'none')) selected @endif>None</option>
                      <option value="in_ground" @if(str_contains($row->portable_system ?? '', 'in_ground')) selected @endif>In ground</option>
                      <option value="portable" @if(str_contains($row->portable_system ?? '', 'portable')) selected @endif>Portable</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="wetting_agents" class="form-label">Wetting Agents Used:</label>
                    <select class="form-select form-control" name="wetting_agents">
                      <option value="no" @if(str_contains($row->wetting_agents ?? '', 'no')) selected @endif>No</option>
                      <option value="yes" @if(str_contains($row->wetting_agents ?? '', 'yes')) selected @endif>Yes</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row my-5">
    <div class="col-12">
      <button type="submit" class="btn btn-primary">Save</button>
    </div>
  </div>
</form>

<script>
document.getElementById('shade_or_sun')?.addEventListener('change', function() {
  if (this.value === 'shade') {
    document.getElementById('percent_shade_wrap')?.classList.remove('invisible');
  } else {
    document.getElementById('percent_shade_wrap')?.classList.add('invisible');
  }
});
</script>